<?php

namespace Ttrig\Billmate\Middlewares;

use Closure;
use Ttrig\Billmate\Exceptions\VerificationException;
use Ttrig\Billmate\Hasher;

class VerifyRequest
{
    public function __construct(public Hasher $hasher)
    {
    }

    public function handle($request, Closure $next)
    {
        if ($this->hasher->verify($request->all())) {
            return $next($request);
        }

        throw new VerificationException(400, 'Bad Request');
    }
}
