<?php

namespace Ttrig\Billmate\Tests\Controllers;

use Illuminate\Support\Facades\Event;
use Mockery as m;
use Ttrig\Billmate\Events\OrderCreated;
use Ttrig\Billmate\Exceptions\BillmateException;
use Ttrig\Billmate\Hasher;
use Ttrig\Billmate\Order;
use Ttrig\Billmate\Service;
use Ttrig\Billmate\Tests\TestCase;

class CallbackControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $this->billmate = $this->mock(Service::class);

        $this->hasher = $this->mock(Hasher::class)
            ->expects()
            ->verify(m::type('array'))
            ->andReturnTrue()
        ;
    }

    public function test_callback_happy_path()
    {
        $order = new Order([
            'number' => '12345',
            'orderid' => 'P12345',
            'method' => Order::PART_PAYMENT,
            'status' => Order::CREATED,
        ]);

        $paymentInfo = $this->makePaymentInfo($order);

        $this->billmate
            ->expects()
            ->getPaymentInfo(m::type(Order::class))
            ->andReturn($paymentInfo)
        ;

        $body = $this->makeRequestBody($order);

        $this->call('POST', route('billmate.callback'), $body)->assertStatus(204);

        Event::assertDispatched(OrderCreated::class, function ($event) use ($order, $paymentInfo) {
            return $event->order['number'] === $order['number']
                && $event->paymentInfo === $paymentInfo;
        });
    }

    public function test_callback_handles_client_error()
    {
        $this->billmate
            ->expects()
            ->getPaymentInfo(m::type(Order::class))
            ->andThrows(BillmateException::class, 'Client error')
        ;

        $body = $this->makeRequestBody(new Order());

        $this->call('POST', route('billmate.callback'), $body)->assertStatus(400);
    }
}
