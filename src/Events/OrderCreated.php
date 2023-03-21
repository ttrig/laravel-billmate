<?php

namespace Ttrig\Billmate\Events;

use Ttrig\Billmate\Order;

class OrderCreated
{
    public function __construct(public Order $order, public array $paymentInfo)
    {
    }
}
