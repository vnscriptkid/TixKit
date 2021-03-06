<?php

namespace Tests\Feature;

use App\Models\Concert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Facades\OrderConfirmationNumber;
use App\Facades\TicketCode;
use App\Mail\OrderConfirmationEmail;
use App\Models\User;
use Database\Factories\ConcertFactory;
use Illuminate\Support\Facades\Mail;

class PurchaseTicketTest extends TestCase
{
    use RefreshDatabase;

    private FakePaymentGateway $paymentGateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentGateway = new FakePaymentGateway();
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
        Mail::fake();
    }

    private function orderTickets($concert, $postData)
    {
        $savedRequest = $this->app['request'];

        $response = $this->json('post', '/concerts/' . $concert->id . '/orders', $postData);

        $this->app['request'] = $savedRequest; // return back to context of old's request

        $this->response = $response;
    }

    private function assertResponseStatus($statusCode)
    {
        $this->response->assertStatus($statusCode);
    }

    private function assertValidationError($field)
    {
        $this->assertResponseStatus(422);
        $this->assertArrayHasKey($field, $this->response->decodeResponseJson()['errors']);
    }

    private function seeJsonSubset($json)
    {
        $this->response->assertJsonFragment($json);
    }

    public function test_user_can_purchase_ticket_to_a_published_concert()
    {
        $this->withoutExceptionHandling();
        // Arrange
        $user = User::factory()->create(['stripe_account_id' => 'test-acc-123']);
        $concert = Concert::factory()->create([
            'ticket_price' => 3740,
            'ticket_quantity' => 3,
            'user_id' => $user->id
        ]);
        $concert->publish();

        OrderConfirmationNumber::shouldReceive('generate')->andReturn('NUMBER123');
        TicketCode::shouldReceive('generateFor')->andReturn('CODE1', 'CODE2', 'CODE3');

        // Act
        $this->orderTickets($concert, [
            'email' => 'john@gmail.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        $this->assertResponseStatus(201);
        $this->seeJsonSubset([
            'email' => 'john@gmail.com',
            'amount' => 3740 * 3,
            'confirmation_number' => 'NUMBER123',
            'tickets' => [
                ['code' => 'CODE1'],
                ['code' => 'CODE2'],
                ['code' => 'CODE3'],
            ]
        ]);
        $this->assertTrue($concert->hasOrderFrom('john@gmail.com'));
        $order = $concert->ordersFrom('john@gmail.com')->first();
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(3740 * 3, $this->paymentGateway->totalChargesFor('test-acc-123'));

        Mail::assertSent(OrderConfirmationEmail::class, function ($mail) use ($order) {
            return $mail->hasTo('john@gmail.com')
                && $mail->order->id === $order->id;
        });
    }

    public function email_is_required_to_purchase_tickets()
    {
        // Arrange
        $concert = Concert::factory(['ticket_quantity' => 3])->create();
        $concert->publish();

        // Act
        $this->orderTickets($concert, [
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        $this->assertValidationError('email');
    }

    function test_email_must_be_valid_to_purchase_tickets()
    {
        // Arrange
        $concert = Concert::factory(['ticket_quantity' => 3])->create();
        $concert->publish();

        // Act
        $this->orderTickets($concert, [
            'email' => 'invalid email',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        $this->assertValidationError('email');
    }

    function test_ticket_quantity_is_required_to_purchase_tickets()
    {
        // Arrange
        $concert = Concert::factory(['ticket_quantity' => 3])->create();
        $concert->publish();

        // Act
        $this->orderTickets($concert, [
            'email' => 'invalid email',
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        $this->assertValidationError('ticket_quantity');
    }

    function test_ticket_quantity_must_be_at_least_1_to_purchase_tickets()
    {
        // Arrange
        $concert = Concert::factory(['ticket_quantity' => 3])->create();
        $concert->publish();

        // Act
        $this->orderTickets($concert, [
            'email' => 'invalid email',
            'ticket_quantity' => 0,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        $this->assertValidationError('ticket_quantity');
    }

    function test_payment_token_is_required()
    {
        // Arrange
        $concert = Concert::factory(['ticket_quantity' => 3])->create();
        $concert->publish();

        // Act
        $this->orderTickets($concert, [
            'email' => 'invalid email',
            'ticket_quantity' => 3
        ]);

        // Assert
        $this->assertValidationError('payment_token');
    }

    function test_an_order_is_not_created_if_payment_fails()
    {
        // Arrange
        $concert = Concert::factory(['ticket_quantity' => 3])->create();
        $concert->publish();

        // Act
        $this->orderTickets($concert, [
            'email' => 'john@gmail.com',
            'ticket_quantity' => 3,
            'payment_token' => 'invalid-payment-token'
        ]);

        // Assert
        $this->assertResponseStatus(422);
        $this->assertFalse($concert->hasOrderFrom('join@gmail.com'));
        $this->assertEquals($concert->ticketsRemaining(), 3);
    }


    public function test_cannot_purchase_tickets_to_an_unpublished_concert()
    {
        // Arrange
        $concert = ConcertFactory::createUnpublished(['ticket_quantity' => 3]);

        // Act
        $this->orderTickets($concert, [
            'email' => 'john@gmail.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        $this->assertResponseStatus(404);
        $this->assertFalse($concert->hasOrderFrom('join@gmail.com'));
        $this->assertEquals($this->paymentGateway->totalCharges(), 0);
    }

    public function test_cannot_purchase_more_tickets_than_remain()
    {
        // Arrange
        $concert = Concert::factory(['ticket_quantity' => 50])->create();
        $concert->publish();

        // Act
        $this->orderTickets($concert, [
            'email' => 'john@gmail.com',
            'ticket_quantity' => 51,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        $this->assertResponseStatus(422);
        $this->assertFalse($concert->hasOrderFrom('join@gmail.com'));
        $this->assertEquals($this->paymentGateway->totalCharges(), 0);
        $this->assertEquals($concert->ticketsRemaining(), 50);
    }

    public function test_2_users_trying_to_compete_the_same_tickets()
    {
        // Arrange
        $concert = Concert::factory(['ticket_quantity' => 2, 'ticket_price' => 1000])->create();
        $concert->publish();

        $this->paymentGateway->beforeFirstCharge(function () use ($concert) {
            // userB start competing
            $this->orderTickets($concert, [
                'email' => 'userComingLater@gmail.com',
                'ticket_quantity' => 1,
                'payment_token' => $this->paymentGateway->getValidTestToken()
            ]);

            $this->assertResponseStatus(422);
            $this->assertFalse($concert->hasOrderFrom('userComingLater@gmail.com'));
            $this->assertEquals($this->paymentGateway->totalCharges(), 0);
            $this->assertEquals($concert->ticketsRemaining(), 0);
        });

        // Act
        $this->orderTickets($concert, [
            'email' => 'userComingFirst@gmail.com',
            'ticket_quantity' => 2,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        $this->assertResponseStatus(201);
        $this->assertTrue($concert->hasOrderFrom('userComingFirst@gmail.com'));
        $this->assertEquals(2, $concert->ordersFrom('userComingFirst@gmail.com')->first()->ticketQuantity());
        $this->assertEquals($this->paymentGateway->totalCharges(), 2000);
    }
}
