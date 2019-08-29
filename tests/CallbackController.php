<?php

namespace Ttrig\Billmate\Tests;

class CallbackController
{
    public function __invoke()
    {
        return request()->all();
    }
}
