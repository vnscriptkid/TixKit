<?php

namespace Tests\Unit;

use App\Models\Concert;
use App\Models\Reservation;
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
}
