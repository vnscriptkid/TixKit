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
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_user_can_purchase_ticket()
    {
        $this->withoutExceptionHandling();
        $paymentGateway = new FakePaymentGateway();
        $this->app->instance(PaymentGateway::class, $paymentGateway);

        // Arrange
        $concert = Concert::factory()->published()->create([
            'ticket_price' => 3740
        ]);

        // Act
        $response = $this->post('/concerts/' . $concert->id . '/orders', [
            'email' => 'john@gmail.com',
            'ticket_quantity' => 3,
            'payment_token' => $paymentGateway->getValidTestToken()
        ]);

        // Assert
        // successful response
        $response->assertStatus(201);
        // user is charged
        $this->assertEquals($paymentGateway->totalCharges(), 3740 * 3);
        // order is created
        $order = $concert->orders()->where(['email' => 'john@gmail.com'])->first();
        $this->assertNotNull($order);
        // correct number of created tickets
        $this->assertEquals(3, $order->tickets()->count());
    }
}
