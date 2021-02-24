<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PromoterLoginTest extends DuskTestCase
{
    use RefreshDatabase;

    public function test_logging_in_successfully()
    {
        User::factory()->create([
            'email' => 'thanh@gmail.com',
            'password' => bcrypt('12345678')
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'thanh@gmail.com')
                ->type('password', '12345678')
                ->press('Log in')
                ->assertPathIs('/backstage/concerts');
        });
    }

    public function test_logging_in_fails()
    {
        User::factory()->create([
            'email' => 'thanh@gmail.com',
            'password' => bcrypt('12345678')
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'thanh@gmail.com')
                ->type('password', 'wrong-password')
                ->press('Log in')
                ->assertPathIs('/login')
                ->assertSee('These credentials do not match our records');
        });
    }
}
