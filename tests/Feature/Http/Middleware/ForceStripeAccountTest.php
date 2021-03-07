<?php

namespace Tests\Feature\Http\Middleware;

use App\Http\Middleware\ForceStripeAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Tests\TestCase;

class ForceStripeAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_a_stripe_account_is_forced_to_connect_to_stripe()
    {
        // Arrange
        $this->be(User::factory()->create([
            'stripe_account_id' => null
        ]));
        $middleware = new ForceStripeAccount();

        // Act
        $response = $middleware->handle(new Request, function ($request) {
            $this->fail("Next was called even though it should not have been.");
        });

        // Assert
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(route('backstage.stripe-connect.connect'), $response->getTargetUrl());
    }
}
