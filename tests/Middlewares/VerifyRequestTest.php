<?php

namespace Ttrig\Billmate\Tests\Middlewares;

use Illuminate\Http\Request;
use Mockery as m;
use Ttrig\Billmate\Exceptions\VerificationException;
use Ttrig\Billmate\Hasher;
use Ttrig\Billmate\Middlewares\VerifyRequest;
use Ttrig\Billmate\Tests\TestCase;

class VerifyRequestTest extends TestCase
{
    private $hasher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hasher = $this->mock(Hasher::class);
    }

    public function test_verifyRequest_with_valid_input()
    {
        $this->hasher->expects()->verify(m::any())->andReturnTrue();

        $middleware = new VerifyRequest($this->hasher);

        $middleware->handle($this->makeRequest(), function ($request) {
            $this->assertNotEmpty($request->credentials);
            $this->assertNotEmpty($request->data);
        });
    }

    public function test_with_invalid_input()
    {
        $this->expectException(VerificationException::class);
        $this->expectExceptionMessage('Bad Request');

        $this->hasher->expects()->verify(m::type('array'))->andReturnFalse();

        $middleware = new VerifyRequest($this->hasher);

        $middleware->handle($this->makeRequest(), function () {
            $this->fail('VerificationException was not thrown');
        });
    }

    protected function makeRequest(): Request
    {
        return new Request([
            'credentials' => [
                'hash' => '...',
            ],
            'data' => [
                'foo' => 'bar',
            ],
        ]);
    }
}
