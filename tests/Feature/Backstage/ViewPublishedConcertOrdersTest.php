<?php

namespace Tests\Feature\Backstage;

use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Database\Factories\ConcertFactory;
use Database\Factories\OrderFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ViewPublishedConcertOrdersTest extends TestCase
{
    use RefreshDatabase;

    function test_a_promoter_can_view_the_orders_of_their_own_published_concert()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = ConcertFactory::createPublished(['user_id' => $user->id]);

        // Act
        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('backstage.published-concert-orders.index');
        $this->assertTrue($response->viewData('concert')->is($concert));
    }

    function test_a_promoter_can_view_10_most_recent_orders_of_the_concert()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = ConcertFactory::createPublished(['user_id' => $user->id]);

        $order11 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('11 days ago')]);
        $order10 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('10 days ago')]);
        $order9 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('9 days ago')]);
        $order8 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('8 days ago')]);
        $order7 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('7 days ago')]);
        $order6 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('6 days ago')]);
        $order5 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('5 days ago')]);
        $order4 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('4 days ago')]);
        $order3 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('3 days ago')]);
        $order2 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('2 days ago')]);
        $order1 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('1 days ago')]);

        // Act
        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        // Assert
        $response->viewData('orders')->assertTheSame([$order1, $order2, $order3, $order4, $order5, $order6, $order7, $order8, $order9, $order10]);
    }

    function test_a_promoter_cannot_view_the_orders_of_unpublished_concerts()
    {
        $user = User::factory()->create();
        $concert = ConcertFactory::createUnpublished(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(404);
    }

    function test_a_promoter_cannot_view_the_orders_of_another_published_concert()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $concertOfOther = ConcertFactory::createUnpublished(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concertOfOther->id}/orders");

        $response->assertStatus(404);
    }

    function test_a_guest_cannot_view_the_orders_of_any_published_concert()
    {
        $concert = ConcertFactory::createPublished();

        $response = $this->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertRedirect('/login');
    }
}
