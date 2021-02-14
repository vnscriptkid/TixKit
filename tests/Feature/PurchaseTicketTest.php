<?php

namespace Tests\Feature;

use App\Models\Concert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;

$paymentGateway = null;

class PurchaseTicketTest extends TestCase
{
    use RefreshDatabase;

    private FakePaymentGateway $paymentGateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentGateway = new FakePaymentGateway();
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    private function orderTickets($concert, $postData)
    {
        return $this->json('post', '/concerts/' . $concert->id . '/orders', $postData);
    }

    private function assertValidationError($response, $field)
    {
        $response->assertStatus(422);
        $this->assertArrayHasKey($field, $response->decodeResponseJson()['errors']);
    }


    public function test_user_can_purchase_ticket_to_a_published_concert()
    {
        // Arrange
        $concert = Concert::factory()->published()->create(['ticket_price' => 3740])->addTickets(3);

        // Act
        $response = $this->orderTickets($concert, [
            'email' => 'john@gmail.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        $response->assertStatus(201);
        $this->assertTrue($concert->hasOrderFrom('john@gmail.com'));
        $this->assertEquals(3, $concert->ordersFrom('john@gmail.com')->first()->ticketCount());
    }

    public function email_is_required_to_purchase_tickets()
    {
        // Arrange
        $concert = Concert::factory()->published()->create()->addTickets(3);

        // Act
        $response = $this->orderTickets($concert, [
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        $this->assertValidationError($response, 'email');
    }

    function test_email_must_be_valid_to_purchase_tickets()
    {
        // Arrange
        $concert = Concert::factory()->published()->create()->addTickets(3);

        // Act
        $response = $this->orderTickets($concert, [
            'email' => 'invalid email',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        $this->assertValidationError($response, 'email');
    }

    function test_ticket_quantity_is_required_to_purchase_tickets()
    {
        // Arrange
        $concert = Concert::factory()->published()->create()->addTickets(3);

        // Act
        $response = $this->orderTickets($concert, [
            'email' => 'invalid email',
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        $this->assertValidationError($response, 'ticket_quantity');
    }

    function test_ticket_quantity_must_be_at_least_1_to_purchase_tickets()
    {
        // Arrange
        $concert = Concert::factory()->published()->create()->addTickets(3);

        // Act
        $response = $this->orderTickets($concert, [
            'email' => 'invalid email',
            'ticket_quantity' => 0,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        $this->assertValidationError($response, 'ticket_quantity');
    }

    function test_payment_token_is_required()
    {
        // Arrange
        $concert = Concert::factory()->published()->create()->addTickets(3);

        // Act
        $response = $this->orderTickets($concert, [
            'email' => 'invalid email',
            'ticket_quantity' => 3
        ]);

        // Assert
        $this->assertValidationError($response, 'payment_token');
    }

    function test_an_order_is_not_created_if_payment_fails()
    {
        // Arrange
        $concert = Concert::factory()->published()->create()->addTickets(3);

        // Act
        $response = $this->orderTickets($concert, [
            'email' => 'john@gmail.com',
            'ticket_quantity' => 3,
            'payment_token' => 'invalid-payment-token'
        ]);

        // Assert
        $response->assertStatus(422);
        $this->assertFalse($concert->hasOrderFrom('join@gmail.com'));
        $this->assertEquals($concert->ticketsRemaining(), 3);
    }


    public function test_cannot_purchase_tickets_to_an_unpublished_concert()
    {
        // Arrange
        $concert = Concert::factory()->unpublished()->create()->addTickets(3);

        // Act
        $response = $this->orderTickets($concert, [
            'email' => 'john@gmail.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        $response->assertStatus(404);
        $this->assertFalse($concert->hasOrderFrom('join@gmail.com'));
        $this->assertEquals($this->paymentGateway->totalCharges(), 0);
    }

    public function test_cannot_purchase_more_tickets_than_remain()
    {
        // Arrange
        $concert = Concert::factory()->published()->create()->addTickets(50);

        // Act
        $response = $this->orderTickets($concert, [
            'email' => 'john@gmail.com',
            'ticket_quantity' => 51,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        $response->assertStatus(422);
        $this->assertFalse($concert->hasOrderFrom('join@gmail.com'));
        $this->assertEquals($this->paymentGateway->totalCharges(), 0);
        $this->assertEquals($concert->ticketsRemaining(), 50);
    }
}
