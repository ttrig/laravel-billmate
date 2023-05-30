<?php

namespace Ttrig\Billmate\Tests\Middlewares;

use Illuminate\Http\Request;
use Ttrig\Billmate\Middlewares\TransformRedirectRequest;
use Ttrig\Billmate\Tests\TestCase;

class TransformRedirectRequestTest extends TestCase
{
    public function test_json_is_decoded_to_array_for_form_requests()
    {
        $request = new Request([
            'credentials' => '{"hash": "123abc"}',
            'data' => '{"foo": "bar"}',
        ]);

        $request->setMethod('POST');
        $request->headers->set('Content-Type', 'application/x-www-form-urlencoded');

        (new TransformRedirectRequest())->handle($request, function ($request) {
            $this->assertEquals(
                [
                    'credentials' => [
                        'hash' => '123abc',
                    ],
                    'data' => [
                        'foo' => 'bar',
                    ],
                ],
                $request->all()
            );
        });
    }
}
