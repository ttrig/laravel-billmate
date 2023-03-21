<?php

namespace Tests\Unit\Billmate;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Mockery as m;
use Ttrig\Billmate\Article;
use Ttrig\Billmate\Checkout;
use Ttrig\Billmate\Exceptions\BillmateException;
use Ttrig\Billmate\Exceptions\VerificationException;
use Ttrig\Billmate\Hasher;
use Ttrig\Billmate\Order;
use Ttrig\Billmate\Service as BillmateService;
use Ttrig\Billmate\Tests\TestCase;

class ServiceTest extends TestCase
{
    private $hasher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hasher = $this->mock(Hasher::class, function ($mock) {
            $mock->expects()->hash(m::type('array'))->andReturn('hash')->byDefault();
            $mock->expects()->verify(m::type('array'))->andReturnTrue()->byDefault();
        });
    }

    private function makeService(): BillmateService
    {
        return new BillmateService($this->hasher);
    }

    public function test_call_sends_request_correctly()
    {
        Carbon::setTestNow();

        Http::fakeSequence()->push([
            'credentials' => [
                'hash' => '3d7506031bac8c67b4fc4750b2f879c6965d595f3',
            ],
            'data' => [
                'foo' => 'bar',
            ],
        ]);

        $result = $this->makeService()->call('foo', ['bar' => 'baz']);

        $this->assertEquals(['foo' => 'bar'], $result);

        Http::assertSent(fn ($request)
            => $request->method() === 'POST'
            && $request->isJson()
            && $request->url() === 'https://api.billmate.se'
            && $request->data() === [
                'credentials' => [
                    'id' => null,
                    'hash' => 'hash',
                    'version' => '2.1.6',
                    'client' => 'ttrig/laravel-billmate',
                    'serverdata' => [
                        'ip' => '127.0.0.1',
                        'referer' => null,
                        'user agent' => 'Symfony',
                        'method' => 'GET',
                        'uri' => 'http://localhost/',
                    ],
                    'time' => now()->timestamp,
                    'test' => '1',
                    'language' => 'en',
                ],
                'data' => [
                    'bar' => 'baz',
                ],
                'function' => 'foo',
            ]);
    }

    public function test_call_throws_exception_on_error_code()
    {
        $this->expectException(BillmateException::class);
        $this->expectExceptionMessage('Billmate Error Code:50014.');

        $this->hasher->expects()->verify()->never();

        Http::fakeSequence()->push([
            'code' => '50014',
            'message' => 'Billmate Error Code:50014.',
            'logid' => '123',
        ]);

        $this->makeService()->call('invalid-request');

        Http::assertSequencesAreEmpty();
    }

    public function test_call_throws_exception_on_unverified_request()
    {
        $this->expectException(VerificationException::class);
        $this->expectExceptionMessage('Invalid response');

        $this->hasher->expects()->verify(m::type('array'))->andReturnFalse();

        Http::fakeSequence()->push(['response']);

        $this->makeService()->call('invalid-response');
    }

    public function test_call_with_non_json_response()
    {
        $this->hasher->expects()->verify()->never();

        Http::fakeSequence()->push('plain text');

        $result = $this->makeService()->call('returns-plain-text');

        $this->assertEquals(['data' => 'plain text'], $result);
    }

    public function test_activatePayment_happy_path()
    {
        Http::fakeSequence()->push([
            'data' => [
                'number' => '123',
                'status' => 'Factoring',
                'orderid' => 'P12345-67',
                'url' => 'https://billmate.localhost/123/456/test',
            ],
        ]);

        $this->makeService()->activatePayment(new Order(['number' => 123]));

        Http::assertSent(fn ($request)
            => $request['data']['number'] === 123
            && $request['function'] === 'activatePayment');
    }

    public function test_initCheckout_happy_path()
    {
        Http::fakeSequence()->push([
            'data' => [
                'number' => '322',
                'status' => 'WaitingForPurchase',
                'orderid' => 'P12345-67',
                'url' => 'https://billmate.localhost/123/456/test',
            ],
        ]);

        $articles = collect([
            new Article([
                'number' => 1000,
                'price' => 100,
                'taxrate' => 0.25,
            ]),
            new Article([
                'number' => 1001,
                'price' => 500,
                'taxrate' => 0.25,
            ]),
        ]);

        $checkout = $this->makeService()->initCheckout($articles);

        $this->assertInstanceOf(Checkout::class, $checkout);
        $this->assertEquals('322', $checkout->number);

        Http::assertSent(fn ($request)
            => preg_match('/^P\d{10}-\d\d$/', $request['data']['PaymentData']['orderid'])
            && count($request['data']['Articles']) === 2
            && $request['data']['Cart']['Total'] === [
                'rounding' => 0,
                'tax' => 15000,
                'withouttax' => 60000,
                'withtax' => 75000,
            ]);
    }

    public function test_initCheckout_with_closure_to_update_data()
    {
        Http::fakeSequence()->push([
            'data' => [
                'number' => '322',
                'status' => 'WaitingForPurchase',
                'orderid' => 'P12345-67',
                'url' => 'https://billmate.localhost/123/456/test',
            ],
        ]);

        $checkout = $this->makeService()->initCheckout(
            collect([new Article()]),
            fn (&$data) => $data['CheckoutData']['terms'] = 'foobar'
        );

        $this->assertInstanceOf(Checkout::class, $checkout);

        Http::assertSent(
            fn ($request) => data_get($request, 'data.CheckoutData.terms') === 'foobar'
        );
    }

    public function test_getPaymentInfo_happy_path()
    {
        Http::fakeSequence()->push(['data' => ['info']]);

        $order = new Order(['number' => '123']);

        $this->assertEquals(['info'], $this->makeService()->getPaymentInfo($order));

        Http::assertSent(fn($request) => $request['data']['number'] === '123');
    }

    public function test_getPaymentPlans_with_article()
    {
        Http::fakeSequence()->push(['data' => ['plans']]);

        $article = new Article([
            'title' => 'Potato',
            'quantity' => 1,
            'price' => 10,
            'taxrate' => 0.25,
        ]);

        $this->assertEquals(['plans'], $this->makeService()->getPaymentPlans($article));

        Http::assertSent(fn ($request) => $request['data'] === [
            'PaymentData' => [
                'currency' => 'SEK',
                'country' => 'SE',
                'language' => 'sv',
                'totalwithtax' => 1250,
            ],
        ]);
    }

    public function test_getPaymentPlans_without_article()
    {
        Http::fakeSequence()->push(['data' => ['plans']]);

        $this->assertEquals(['plans'], $this->makeService()->getPaymentPlans());

        Http::assertSent(fn ($request) => $request['data'] === [
            'PaymentData' => [
                'currency' => 'SEK',
                'country' => 'SE',
                'language' => 'sv',
                'totalwithtax' => null,
            ],
        ]);
    }
}
