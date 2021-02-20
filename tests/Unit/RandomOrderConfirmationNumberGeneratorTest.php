<?php

namespace Tests\Unit;

use App\RandomOrderConfirmationNumberGenerator;
use PHPUnit\Framework\TestCase;

class RandomOrderConfirmationNumberGeneratorTest extends TestCase
{
    public function test_must_be_24_characters()
    {
        $generator = new RandomOrderConfirmationNumberGenerator;

        $random = $generator->generate();

        $this->assertEquals(strlen($random), 24);
    }

    public function test_must_contain_only_uppercase_letters_and_numbers()
    {
        $generator = new RandomOrderConfirmationNumberGenerator;

        $random = $generator->generate();

        $this->assertMatchesRegularExpression('/^[A-Z0-9]+$/', $random);
    }

    public function test_must_contain_no_ambiguous_characters()
    {
        $generator = new RandomOrderConfirmationNumberGenerator;

        $random = $generator->generate();

        $this->assertFalse(strpos($random, '0'));
        $this->assertFalse(strpos($random, 'O'));
        $this->assertFalse(strpos($random, '1'));
        $this->assertFalse(strpos($random, 'I'));
    }

    public function test_must_generate_unique_string()
    {
        $generator = new RandomOrderConfirmationNumberGenerator;

        $confirmationNumbers = array_map(function () use ($generator) {
            return $generator->generate();
        }, range(1, 100000));

        $this->assertCount(100000, array_unique($confirmationNumbers));
    }
}
