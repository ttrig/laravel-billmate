<?php

namespace Ttrig\Billmate\Tests;

class CheckoutController
{
    public function index()
    {
        return view('billmate::iframe', ['src' => 'http://billlmate.localhost/123']);
    }
}
