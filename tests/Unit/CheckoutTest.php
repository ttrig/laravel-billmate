<?php

namespace Ttrig\Billate\Tests\Unit;

use Ttrig\Billmate\Checkout;
use Ttrig\Billmate\Tests\TestCase;

class CheckoutTest extends TestCase
{
    public function test_iframe()
    {
        $checkout = new Checkout(['url' => 'http://billmate.localhost/123']);

        $html = $checkout->iframe()->render();

        $this->assertStringContainsString('<iframe', $html);
        $this->assertStringContainsString('http://billmate.localhost/123', $html);
    }
}
