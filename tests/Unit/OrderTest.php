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
            ['isCancelled', Order::CANCELLED],
            ['isCreated', Order::CREATED],
            ['isFailed', Order::FAILED],
            ['isPaid', Order::PAID],
            ['isPending', Order::PENDING],
        ];
    }
}
