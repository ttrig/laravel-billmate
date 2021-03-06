<?php

namespace Ttrig\Billmate\Tests;

use GrahamCampbell\TestBenchCore\ServiceProviderTrait;
use Ttrig\Billmate\Service as BillmateService;
use Ttrig\Billmate\Controllers\CallbackController;

class ServiceProviderTest extends TestCase
{
    use ServiceProviderTrait;

    public function test_billmateService_is_injectable()
    {
        $this->assertIsInjectable(BillmateService::class);
    }

    public function test_routes_are_added()
    {
        $this->assertEquals('http://localhost/billmate/accept', route('billmate.accept'));
        $this->assertEquals('http://localhost/billmate/cancel', route('billmate.cancel'));
        $this->assertEquals('http://localhost/billmate/callback', route('billmate.callback'));
    }

    public function test_views_are_loaded()
    {
        $this->assertNotEmpty(view('billmate::iframe'));
    }

    /**
     * @dataProvider defaultConfigProvider
     */
    public function test_default_config($value, $configKey)
    {
        $this->assertEquals($value, $this->app->config->get($configKey));
    }

    public function defaultConfigProvider()
    {
        return [
            ['billmate', 'billmate.route_prefix'],
            ['BillmateController@accept', 'billmate.accept_action'],
            ['BillmateController@cancel', 'billmate.cancel_action'],
            [CallbackController::class, 'billmate.callback_action'],
        ];
    }
}
