<?php

namespace Ttrig\Billmate\Tests;

use GrahamCampbell\TestBench\AbstractPackageTestCase;
use Ttrig\Billmate\Order;
use Ttrig\Billmate\ServiceProvider;

abstract class TestCase extends AbstractPackageTestCase
{
    protected static function getServiceProviderClass(): string
    {
        return ServiceProvider::class;
    }

    protected function makeRequestBody(Order $order): array
    {
        return [
            'credentials' => [
                'hash' => '...',
            ],
            'data' => [
                'number' => $order->number ?: '1000',
                'status' => $order->status ?: 'Created',
                'orderid' => $order->orderid ?: 'P1234',
                'url' => empty($order) || ($order->cancelled() || $order->failed())
                    ? ''
                    : 'https://billmate.localhost/invoice/12345',
            ],
        ];
    }

    protected function makePaymentInfo(Order $order): array
    {
        return [
            "credentials" => [
                'hash' => '...',
            ],
            'data' => [
                'PaymentData' => [
                    'method' => $order->method ?: Order::CARD,
                    'paymentplanid' => '',
                    'currency' => 'SEK',
                    'country' => 'SE',
                    'language' => 'sv',
                    'autoactivate' => '0',
                    'orderid' => $order->orderid,
                    'status' => $order->status,
                    'paymentid_related' => '',
                    'url' => '',
                ],
                'PaymentInfo' => [
                    'paymentdate' => now()->toDateString(),
                    'paymentterms' => '14',
                    'yourreference' => 'Purchaser X',
                    'ourreference' => 'Seller Y',
                    'projectname' => 'Project Z',
                    'deliverymethod' => 'Post',
                    'deliveryterms' => 'FOB',
                ],
                'Settlement' => [
                    'number' => '2',
                    'date' => now()->toDateString(),
                ],
                'Customer' => [
                    'nr' => '123',
                    'pno' => '5501011018',
                    'Billing' => [
                        'firstname' => 'Firstname',
                        'lastname' => 'Lastname',
                        'company' => 'Company',
                        'street' => 'Street',
                        'street2' => 'Street2',
                        'zip' => '12345',
                        'city' => 'Teststad',
                        'country' => 'Sweden',
                        'phone' => '0123-456789',
                        'email' => 'test@developer',
                    ],
                ],
                'Articles' => [
                    [
                        'artnr' => '',
                        'title' => 'Article 1',
                        'quantity' => '1',
                        'aprice' => '2000',
                        'tax' => '0',
                        'discount' => '0',
                        'withouttax' => '2000',
                        'taxrate' => '0',
                    ],
                ],
                'Cart' => [
                    'Total' => [
                        'rounding' => '0',
                        'withouttax' => '2000',
                        'tax' => '0',
                        'withtax' => '2000',
                    ],
                ],
            ],
        ];
    }
}
