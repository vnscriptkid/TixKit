<?php

namespace Tests\Unit;

use App\HashidsTicketCodeGenerator;
use App\Models\Ticket;
use Tests\TestCase;

class HashidsTicketCodeGeneratorTest extends TestCase
{
    public function test_code_is_at_least_6_characters()
    {
        $generator = new HashidsTicketCodeGenerator('salt1');

        $code = $generator->generateFor(new Ticket(['id' => 1]));

        $this->assertGreaterThanOrEqual(strlen($code), 6);
    }

    public function test_code_contains_uppercase_letters()
    {
        $generator = new HashidsTicketCodeGenerator('salt1');

        $code = $generator->generateFor(new Ticket(['id' => 1]));

        $this->assertMatchesRegularExpression('/^[A-Z]+$/', $code);
    }

    public function test_different_ids_generate_different_code()
    {
        $generator = new HashidsTicketCodeGenerator('salt1');

        $code1 = $generator->generateFor(new Ticket(['id' => 1]));
        $code2 = $generator->generateFor(new Ticket(['id' => 2]));

        $this->assertNotEquals($code1, $code2);
    }

    public function test_same_ids_generate_same_code()
    {
        $generator = new HashidsTicketCodeGenerator('salt1');

        $code1 = $generator->generateFor(new Ticket(['id' => 1]));
        $code2 = $generator->generateFor(new Ticket(['id' => 1]));

        $this->assertEquals($code1, $code2);
    }

    public function test_same_ids_with_different_salts_generate_different_code()
    {
        $generator1 = new HashidsTicketCodeGenerator('salt1');
        $generator2 = new HashidsTicketCodeGenerator('salt2');

        $code1 = $generator1->generateFor(new Ticket(['id' => 1]));
        $code2 = $generator2->generateFor(new Ticket(['id' => 1]));

        $this->assertNotEquals($code1, $code2);
    }
}
