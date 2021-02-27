<?php

namespace Tests\Feature\Backstage;

use App\Models\Concert;
use App\Models\User;
use Database\Factories\ConcertFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

class ViewConcertList extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        TestResponse::macro('viewData', function ($key) {
            return $this->original->getData()[$key];
        });

        Collection::macro('assertContains', function ($value) {
            Assert::assertTrue(
                $this->contains($value),
                "Failed asserting that the collection contained the specified value."
            );
        });

        Collection::macro('assertNotContains', function ($value) {
            Assert::assertFalse(
                $this->contains($value),
                "Failed asserting that the collection did not contain the specified value."
            );
        });

        Collection::macro('assertContainsExactly', function ($arr) {
            Assert::assertEquals(count($arr), $this->count(), "Size does not match.");
            foreach ($arr as $item) {
                Assert::assertTrue($this->contains($item), 'Collection does not contain this item.');
            }
        });
    }

    public function test_guests_can_not_view_promoters_concert_list()
    {
        $response = $this->get('/backstage/concerts');

        $response->assertRedirect('/login');
    }

    public function test_promoters_can_view_his_concert_list_but_not_others()
    {
        $this->withoutExceptionHandling();
        // Arrange
        $me = User::factory()->create();
        $otherUser = User::factory()->create();

        $concertA = Concert::factory()->unpublished()->create(['user_id' => $me->id]);
        $concertB = Concert::factory()->create(['user_id' => $otherUser->id]);
        $concertC = ConcertFactory::createPublished(['user_id' => $otherUser->id]);
        $concertD = ConcertFactory::createPublished(['user_id' => $me->id]);
        $concertE = Concert::factory()->unpublished()->create(['user_id' => $me->id]);

        // Act
        $response = $this->actingAs($me)->get('/backstage/concerts');

        // Assert
        $response->assertStatus(200);

        $response->viewData('unpublishedConcerts')->assertContainsExactly([$concertA, $concertE]);

        $response->viewData('publishedConcerts')->assertContainsExactly([$concertD]);
    }
}
