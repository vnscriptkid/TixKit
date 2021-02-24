<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PromoterLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'thanh@gmail.com',
            'password' => bcrypt('12345678')
        ]);

        $response = $this->post('/login', [
            'email' => 'thanh@gmail.com',
            'password' => '12345678'
        ]);

        $response->assertRedirect('/backstage/concerts/new');
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
    }

    public function test_login_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'thanh@gmail.com',
            'password' => bcrypt('12345678')
        ]);

        $response = $this->post('/login', [
            'email' => 'thanh@gmail.com',
            'password' => 'wrong-password'
        ]);

        $response->assertRedirect('/login');
        $this->assertFalse(Auth::check());
        $response->assertSessionHasErrors(['email']);
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
    }

    public function test_login_with_nonexistent_email()
    {
        User::factory()->create([
            'email' => 'thanh@gmail.com',
            'password' => bcrypt('12345678')
        ]);

        $response = $this->post('/login', [
            'email' => 'linh@gmail.com',
            'password' => 'wrong-password'
        ]);

        $response->assertRedirect('/login');
        $this->assertFalse(Auth::check());
        $response->assertSessionHasErrors(['email']);
    }

    public function test_logging_out_successfully()
    {
        Auth::login(User::factory()->create());
        $this->assertTrue(Auth::check());

        $this->post('/logout')->assertRedirect('/login');

        $this->assertFalse(Auth::check());
    }
}
