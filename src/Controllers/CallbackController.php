<?php

namespace Ttrig\Billmate\Controllers;

use Ttrig\Billmate\Events\OrderCreated;
use Ttrig\Billmate\Exceptions\BillmateException;
use Ttrig\Billmate\Order;
use Ttrig\Billmate\Service;

class CallbackController
{
    public function __invoke(Service $billmate)
    {
        $order = new Order(request()->data);

        try {
            $paymentInfo = $billmate->getPaymentInfo($order);
        } catch (BillmateException $exception) {
            logger()->critical($exception->getMessage());

            return response(null, 400);
        }

        event(new OrderCreated($order, $paymentInfo));

        return response(null, 204);
    }
}
