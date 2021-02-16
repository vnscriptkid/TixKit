<?php

namespace Tests\Unit;

use App\Models\Concert;
use App\Models\Reservation;
use App\Models\Ticket;
use Carbon\Carbon;
use Mockery;
use Mockery\LegacyMockInterface;
use Tests\TestCase;

class ReservationModelTest extends TestCase
{
    public function test_calculating_total_price()
    {
        // Arrange
        $tickets = collect([
            (object) ['price' => 1000],
            (object) ['price' => 1000],
        ]);
        // Act
        $reservation = new Reservation($tickets);

        // Assert
        $this->assertEquals($reservation->totalPrice(), 2000);
    }

    public function test_reservation_can_be_cancelled()
    {
        // Arrange
        $tickets = collect([
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
        ]);
        // Act
        $reservation = new Reservation($tickets);
        $reservation->cancel();

        // Assert
        $tickets->each(function (LegacyMockInterface $ticket) {
            $ticket->shouldHaveReceived('release');
        });
    }

    public function test_get_tickets()
    {
        // Arrange
        $tickets = collect([
            (object) ['price' => 1000],
            (object) ['price' => 1000],
        ]);
        // Act
        $reservation = new Reservation($tickets);

        // Assert
        $this->assertEquals($reservation->tickets(), $tickets);
    }
}
