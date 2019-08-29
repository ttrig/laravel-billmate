<?php

namespace Ttrig\Billmate;

use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Ttrig\Billmate\Article;
use Ttrig\Billmate\Exceptions\BillmateException;
use Ttrig\Billmate\Exceptions\VerificationException;

class Service
{
    const CURRENCY_SEK = 'SEK';
    const COUNTRY_SE = 'SE';
    const LANGUAGE_SV = 'sv';

    private $client;
    private $config;

    public function __construct(Client $client, Hasher $hasher)
    {
        $this->client = $client;
        $this->hasher = $hasher;
        $this->config = config('billmate');
    }

    public function initCheckout(Collection $articles, \Closure $callback = null): ?Checkout
    {
        $totalWithoutTax = $articles->sum->price();
        $totalTax = $articles->sum->totalTax();
        $totalWithTax = $articles->sum->totalWithTax();

        $articlesData = $articles->map(function (Article $article) {
            return $article->paymentData();
        });

        $data = [
            'CheckoutData' => [
                'windowmode' => 'iframe',
                'redirectOnSuccess' => 'true',
                'terms' => 'https://www.billmate.se/billmates-checkout-anvandarvillkor',
                'privacyPolicy' => 'https://www.billmate.se/integritetspolicy',
                'companyView' => 'false',
            ],
            'PaymentData' => [
                'currency' => static::CURRENCY_SEK,
                'country' => static::COUNTRY_SE,
                'language' => static::LANGUAGE_SV,
                'orderid' => 'P' . now()->getTimestamp() . '-' . rand(10, 99),
                'autoactivate' => '0',
                'accepturl' => route('billmate.accept'),
                'cancelurl' => route('billmate.cancel'),
                'callbackurl' => route('billmate.callback'),
            ],
            'Articles' => $articlesData,
            'Cart' => [
                'Total' => [
                    'rounding' => 0,
                    'tax' => $totalTax,
                    'withouttax' => $totalWithoutTax,
                    'withtax' => $totalWithTax,
                ],
            ],
        ];

        if ($callback) {
            $callback($data);
        }

        $checkoutData = $this->post('initCheckout', $data);

        return new Checkout($checkoutData);
    }

    public function getPaymentInfo(Order $order): array
    {
        return $this->post('getPaymentinfo', [
            'number' => $order->number,
        ]);
    }

    private function post(string $function, array $data): array
    {
        $postData = [
            'credentials' => [
                'id' => $this->config['id'],
                'hash' => $this->hasher->hash($data),
                'version' => $this->config['version'],
                'client' => $this->config['client'],
                'serverdata' => $this->getServerData(),
                'time' => microtime(true),
                'test' => $this->config['test'] ? 1 : 0,
                'language' => app()->getLocale(),
            ],
            'data' => $data,
            'function' => $function,
        ];

        $response = $this->client->post($this->config['url'], [
            'json' => $postData,
        ]);

        $arrayResponse = json_decode($response->getBody(), true);

        if (isset($arrayResponse['code'])) {
            throw new BillmateException(
                $arrayResponse['message'] ?? 'Error code ' . $arrayResponse['code']
            );
        }

        if (! $this->hasher->verify($arrayResponse)) {
            throw new VerificationException(400, 'Invalid response');
        }

        return $arrayResponse['data'] ?? [];
    }

    private function getServerData(): array
    {
        $request = request();

        return [
            'ip' => $request->getClientIp(),
            'referer' => $request->server->get('HTTP_REFERER'),
            'user agent' => $request->server->get('HTTP_USER_AGENT'),
            'method' => $request->getMethod(),
            'uri' => $request->getUri(),
        ];
    }
}
