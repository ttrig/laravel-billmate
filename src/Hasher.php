<?php

namespace Ttrig\Billmate;

class Hasher
{
    public function hash(array $data): string
    {
        return hash_hmac(
            'sha512',
            json_encode($data),
            config('billmate.key'),
        );
    }

    public function verify(array $content): bool
    {
        if (empty($content['data']) || empty($content['credentials'])) {
            return false;
        }

        $hash = $this->hash($content['data']);

        if (data_get($content['credentials'], 'hash') === $hash) {
            return true;
        }

        return false;
    }
}
