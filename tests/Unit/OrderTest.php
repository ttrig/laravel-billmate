<?php

namespace Ttrig\Billate\Tests\Unit;

use Ttrig\Billmate\Order;
use Ttrig\Billmate\Tests\TestCase;

class OrderTest extends TestCase
{
    /**
     * @dataProvider statusProvider
     */
    public function test_statuses($method, $status)
    {
        $order = new Order();

        $this->assertFalse($order->$method());

        $order->status = $status;

        $this->assertTrue($order->$method());
    }

    public function statusProvider()
    {
        return [
            ['cancelled', Order::CANCELLED],
            ['created', Order::CREATED],
            ['failed', Order::FAILED],
            ['paid', Order::PAID],
            ['pending', Order::PENDING],
        ];
    }
}
