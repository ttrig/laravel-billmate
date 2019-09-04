<?php

namespace Tests\Unit\Billmate;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Mockery as m;
use Ttrig\Billmate\Article;
use Ttrig\Billmate\Exceptions\BillmateException;
use Ttrig\Billmate\Exceptions\VerificationException;
use Ttrig\Billmate\Hasher;
use Ttrig\Billmate\Order;
use Ttrig\Billmate\Service as BillmateService;
use Ttrig\Billmate\Tests\TestCase;

class ServiceTest extends TestCase
{
    private $hasher;
    private $history;
    private $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hasher = $this->mock(Hasher::class);
    }

    private function makeService(
        array $responseData = [],
        ?bool $verified = true
    ): BillmateService {
        $this->hasher->expects()->hash(m::type('array'))->once()->andReturn('...');

        if (is_bool($verified)) {
            $this->hasher->expects()->verify(m::type('array'))->andReturn($verified);
        }

        $response = new Response(200, [], json_encode($responseData));

        return new BillmateService($this->makeClient($response), $this->hasher);
    }

    private function makeClient(?Response $response = null): Client
    {
        $this->container = [];
        $this->history = Middleware::history($this->container);

        $mock = new MockHandler($response ? [$response] : null);

        $handler = HandlerStack::create($mock);
        $handler->push($this->history);

        $client = new Client(['handler' => $handler]);

        return $client;
    }

    public function test_activatePayment_happy_path()
    {
        $this->makeService()->activatePayment(new Order());

        $this->assertEquals(1, count($this->container));
    }

    public function test_initCheckout_happy_path()
    {
        $billmate = $this->makeService([
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

        $billmate->initCheckout($articles);

        $this->assertEquals(1, count($this->container));

        $request = data_get($this->container, '0.request');
        $requestBody = json_decode($request->getBody(), true);

        $this->assertEquals(15000, data_get($requestBody, 'data.Cart.Total.tax'));
        $this->assertEquals(60000, data_get($requestBody, 'data.Cart.Total.withouttax'));
        $this->assertEquals(75000, data_get($requestBody, 'data.Cart.Total.withtax'));
    }

    public function test_initCheckout_with_closure_to_update_data()
    {
        $this->makeService()->initCheckout(
            collect([new Article()]),
            function (&$data) {
                data_set($data, 'CheckoutData.terms', 'foobar');
            }
        );

        $this->assertEquals(1, count($this->container));

        $lastRequest = data_get($this->container, '0.request');
        $lastRequestBody = json_decode($lastRequest->getBody(), true);

        $this->assertEquals('foobar', data_get($lastRequestBody, 'data.CheckoutData.terms'));
    }

    public function test_getPaymentInfo_happy_path()
    {
        $this->makeService()->getPaymentInfo(new Order());

        $this->assertEquals(1, count($this->container));
    }

    public function test_getPaymentPlans_with_article()
    {
        $this->makeService()->getPaymentPlans(new Article());

        $this->assertEquals(1, count($this->container));
    }

    public function test_getPaymentPlans_without_article()
    {
        $this->makeService()->getPaymentPlans();

        $this->assertEquals(1, count($this->container));
    }

    public function test_call_throws_exception_on_error_code()
    {
        $this->expectException(BillmateException::class);
        $this->expectExceptionMessage('Billmate Error Code:50014.');

        $response = [
            'code' => '50014',
            'message' => 'Billmate Error Code:50014.',
            'logid' => '123',
        ];

        $this->makeService($response, null)->call('invalid-request');
    }

    public function test_call_throws_exception_on_unverified_request()
    {
        $this->expectException(VerificationException::class);
        $this->expectExceptionMessage('Invalid response');

        $this->makeService([], false)->call('invalid-response');
    }

    public function test_call_with_non_json_response()
    {
        $this->hasher->expects()->hash(m::type('array'))->andReturn('...');

        $response = new Response(200, [], 'plain text');

        $billmate = new BillmateService($this->makeClient($response), $this->hasher);

        $data = $billmate->call('returns-plain-text');

        $this->assertEquals(1, count($this->container));
        $this->assertEquals(['data' => 'plain text'], $data);
    }
}
