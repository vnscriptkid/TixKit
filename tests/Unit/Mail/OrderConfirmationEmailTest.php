<?php

namespace Tests\Unit\Mail;

use App\Mail\OrderConfirmationEmail;
use App\Models\Order;
use Tests\TestCase;

class OrderConfirmationEmailTest extends TestCase
{
    public function test_mail_contains_link_back_to_order()
    {
        $order = Order::factory()->make([
            'confirmation_number' => 'CONFIRMATIONNUMBER456',
            'concert_id' => 1
        ]);
        $mail = new OrderConfirmationEmail($order);
        $this->assertStringContainsString(url("/orders/CONFIRMATIONNUMBER456"), $mail->render());
    }

    public function test_mail_has_subject()
    {
        $order = Order::factory()->make([
            'confirmation_number' => 'CONFIRMATIONNUMBER456',
            'concert_id' => 1
        ]);
        $mail = new OrderConfirmationEmail($order);
        $this->assertEquals($mail->build()->subject, '#TixKit Order Confirmation');
    }
}
