<?php

namespace Tests\Feature\Http\Middleware;

use App\Http\Middleware\ForceStripeAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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

    public function test_user_with_a_stripe_account_can_continue()
    {
        // Arrange
        $this->be(User::factory()->create([
            'stripe_account_id' => 'a-valid-stripe-account'
        ]));
        $middleware = new ForceStripeAccount();

        $next = new class
        {
            public $called = false;

            public function __invoke($request)
            {
                $this->called = true;
                return $request;
            }
        };

        $request = new Request;

        // Act
        $response = $middleware->handle($request, $next);

        // Assert
        $this->assertTrue($next->called);
        $this->assertSame($request, $response);
    }

    public function test_middleware_is_applied_to_all_backstage_routes()
    {
        $routes = [
            'backstage.concerts.new',
            'backstage.concerts.edit',
            'backstage.concerts.update',
            'backstage.concerts.store',
            'backstage.concerts.index',
            'backstage.published-concerts.store',
            'backstage.published-concert-orders.index',
            'backstage.concert-messages.new',
            'backstage.concert-messages.store',
        ];

        foreach ($routes as $route) {
            $this->assertContains(
                ForceStripeAccount::class,
                Route::getRoutes()->getByName($route)->gatherMiddleware()
            );
        }
    }
}
