<?php

namespace Ttrig\Billmate\Middlewares;

use Closure;
use Illuminate\Foundation\Http\Middleware\TransformsRequest;

/**
 * Because Billmate sends FORM data with JSON strings to "accepturl"
 * and "cancelurl"(?) but raw JSON to "callbackurl", we need to make
 * sure we are working with arrays.
 */
class TransformRedirectRequest extends TransformsRequest
{
    public function handle($request, Closure $next)
    {
        if (! $request->isJson()) {
            $this->clean($request);
        }

        return $next($request);
    }

    protected function transform($key, $value)
    {
        return json_decode($value, true);
    }
}
