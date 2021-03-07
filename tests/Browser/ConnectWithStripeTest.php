<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Stripe\Account;
use Tests\DuskTestCase;

class ConnectWithStripeTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_promoter_connecting_a_stripe_account_successfully()
    {
        $user = User::factory()->create([
            'stripe_account_id' => null,
            'stripe_access_token' => null
        ]);

        $this->browse(function (Browser $browser) use ($user) {

            $browser->loginAs($user)
                ->visit('/backstage/stripe-connect/connect')
                ->clickLink('Connect with Stripe')
                ->assertUrlIs('https://connect.stripe.com/oauth/v2/authorize')
                ->assertQueryStringHas('response_type', 'code')
                ->assertQueryStringHas('scope', 'read_write')
                ->assertQueryStringHas('client_id', config('services.stripe.client_id'))
                ->waitForText('Skip this form')
                ->click('#skip-account-app')
                ->waitForRoute('backstage.concerts.index')
                ->assertRouteIs('backstage.concerts.index');

            tap($user->fresh(), function ($user) {
                $this->assertNotNull($user->stripe_account_id);
                $this->assertNotNull($user->stripe_access_token);

                $connectedAccount = Account::retrieve(null, [
                    'api_key' => $user->stripe_access_token
                ]);

                $this->assertEquals($user->stripe_account_id, $connectedAccount->id);
            });
        });
    }
}
