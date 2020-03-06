<?php

namespace Ttrig\Billmate;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Ttrig\Billmate\Article;
use Ttrig\Billmate\Exceptions\BillmateException;
use Ttrig\Billmate\Exceptions\VerificationException;

class Service
{
    public const CURRENCY_SEK = 'SEK';
    public const COUNTRY_SE = 'SE';
    public const LANGUAGE_SV = 'sv';

    private $hasher;
    private $config;

    public function __construct(Hasher $hasher)
    {
        $this->hasher = $hasher;
        $this->config = config('billmate');
    }

    public function activatePayment(Order $order): array
    {
        return $this->call('activatePayment', [
            'number' => $order->number,
        ]);
    }

    public function initCheckout(
        Collection $articles,
        Closure $callback = null
    ): ?Checkout {
        $totalWithoutTax = $articles->sum->price();
        $totalTax = $articles->sum->totalTax();
        $totalWithTax = $totalTax + $totalWithoutTax;
        $articlesData = $articles->map->paymentData()->toArray();

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

        $checkoutData = $this->call('initCheckout', $data);

        return new Checkout($checkoutData);
    }

    public function getPaymentInfo(Order $order): array
    {
        return $this->call('getPaymentinfo', [
            'number' => $order->number,
        ]);
    }

    public function getPaymentPlans(Article $article = null): array
    {
        return $this->call('getPaymentplans', [
            'PaymentData' => [
                'currency' => static::CURRENCY_SEK,
                'country' => static::COUNTRY_SE,
                'language' => static::LANGUAGE_SV,
                'totalwithtax' => $article ? $article->totalWithTax() : null,
            ],
        ]);
    }

    public function call(string $function, array $data = []): array
    {
        $postData = [
            'credentials' => [
                'id' => $this->config['id'],
                'hash' => $this->hasher->hash($data),
                'version' => $this->config['version'],
                'client' => $this->config['client'],
                'serverdata' => $this->getServerData(),
                'time' => now()->timestamp,
                'test' => $this->config['test'] ? '1' : '0',
                'language' => app()->getLocale(),
            ],
            'data' => $data,
            'function' => $function,
        ];

        $response = Http::post($this->config['url'], $postData);

        if (! $response->json()) {
            return ['data' => $response->body()];
        }

        if (isset($response['code'])) {
            throw new BillmateException(
                $response['message'] ?? 'Error code ' . $response['code']
            );
        }

        if (! $this->hasher->verify($response->json())) {
            throw new VerificationException(400, 'Invalid response');
        }

        return $response['data'] ?? [];
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
