<?php

namespace Ttrig\Billmate\Tests\Controllers;

use Mockery as m;
use Ttrig\Billmate\Hasher;
use Ttrig\Billmate\Order;
use Ttrig\Billmate\Tests\TestCase;

class RedirectControllerTest extends TestCase
{
    private $hasher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hasher = $this->mock(Hasher::class);
    }

    protected function getEnvironmentSetUp($app)
    {
        $app->config->set('billmate.accept_action', 'Ttrig\Billmate\Tests\RedirectController@accept');
        $app->config->set('billmate.cancel_action', 'Ttrig\Billmate\Tests\RedirectController@cancel');
    }

    public function test_accept_sad_path()
    {
        $this->hasher->expects()->verify(m::any())->andReturnFalse();

        $this->call('POST', route('billmate.accept'))->assertStatus(400);
    }

    public function test_accept_happy_path()
    {
        $this->hasher->expects()->verify(m::type('array'))->andReturnTrue();

        $order = new Order(['number' => 1000]);
        $body = $this->makeRequestBody($order);

        $this->call('POST', route('billmate.accept'), $body)
            ->assertOk()
            ->assertSeeText('accept order 1000');
    }

    public function test_cancel_sad_path()
    {
        $this->hasher->expects()->verify(m::type('array'))->andReturnFalse();

        $this->call('POST', route('billmate.cancel'))->assertStatus(400);
    }

    public function test_cancel_happy_path()
    {
        $this->hasher->expects()->verify(m::type('array'))->andReturnTrue();

        $order = new Order(['number' => 1000]);
        $body = $this->makeRequestBody($order);

        $this->call('POST', route('billmate.cancel'), $body)
            ->assertOk()
            ->assertSeeText('cancel order 1000');
    }

    protected function makeRequestBody(Order $order): array
    {
        $body = parent::makeRequestBody($order);

        $body['credentials'] = json_encode($body['credentials']);
        $body['data'] = json_encode($body['data']);

        return $body;
    }
}
