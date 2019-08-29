<?php

namespace Ttrig\Billmate\Tests;

class RedirectController
{
    public function accept()
    {
        return 'accept order ' . request()->data['number'];
    }

    public function cancel()
    {
        return 'cancel order ' . request()->data['number'];
    }
}
