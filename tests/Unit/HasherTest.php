<?php

namespace Ttrig\Billate\Tests\Unit;

use Ttrig\Billmate\Hasher;
use Ttrig\Billmate\Tests\TestCase;

class HasherTest extends TestCase
{
    public function test_hash()
    {
        $hash = (new Hasher())->hash(['data']);
        $this->assertEquals(128, strlen($hash));
    }

    public function test_verify_with_valid_hash()
    {
        $hasher = new Hasher();

        $hash = $hasher->hash(['foo' => 'bar']);

        $data = [
            'credentials' => [
                'hash' => $hash,
            ],
            'data' => [
                'foo' => 'bar'
            ],
        ];

        $this->assertTrue($hasher->verify($data));
    }

    public function test_verifyHash_with_invalid_hash()
    {
        $hasher = new Hasher();

        $hash = $hasher->hash(['foo' => 'bar']);

        $data = [
            'credentials' => [
                'hash' => $hash,
            ],
            'data' => [
                'bar' => 'foo'
            ],
        ];

        $this->assertFalse($hasher->verify($data));
    }

    public function test_verifyHash_without_data()
    {
        $this->assertFalse((new Hasher())->verify([]));
    }
}
