<?php

namespace Ttrig\Billmate;

use Illuminate\Support\Fluent;

class Checkout extends Fluent
{
    public function iframe()
    {
        return view('billmate::iframe')->with(['src' => $this->url]);
    }
}
