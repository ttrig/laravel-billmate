<?php

namespace Ttrig\Billmate\Events;

use Ttrig\Billmate\Order;

class OrderCreated
{
    public $order;
    public $paymentInfo;

    public function __construct(Order $order, array $paymentInfo)
    {
        $this->order = $order;
        $this->paymentInfo = $paymentInfo;
    }
}
