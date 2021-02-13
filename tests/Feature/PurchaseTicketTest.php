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

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentGateway = new FakePaymentGateway();
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    public function test_user_can_purchase_ticket()
    {
        // Arrange
        $concert = Concert::factory()->published()->create([
            'ticket_price' => 3740
        ]);

        // Act
        $response = $this->post('/concerts/' . $concert->id . '/orders', [
            'email' => 'john@gmail.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        // successful response
        $response->assertStatus(201);
        // user is charged
        $this->assertEquals($this->paymentGateway->totalCharges(), 3740 * 3);
        // order is created
        $order = $concert->orders()->where(['email' => 'john@gmail.com'])->first();
        $this->assertNotNull($order);
        // correct number of created tickets
        $this->assertEquals(3, $order->tickets()->count());
    }

    public function test_missing_email_in_request()
    {
        // Arrange
        $concert = Concert::factory()->published()->create([
            'ticket_price' => 3740
        ]);

        // Act
        $response = $this->json('post', '/concerts/' . $concert->id . '/orders', [
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        $response->assertStatus(422);
        $this->assertArrayHasKey('email', $response->decodeResponseJson()['errors']);
    }
}
