<?php

namespace Ttrig\Billate\Tests\Unit;

use Ttrig\Billmate\Hasher;
use Ttrig\Billmate\Tests\TestCase;

class HasherTest extends TestCase
{
    private $hasher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hasher = new Hasher();
    }

    public function test_hash()
    {
        $hash = $this->hasher->hash(['data']);

        $this->assertEquals(128, strlen($hash));
    }

    public function test_verify_with_valid_hash()
    {
        $hash = $this->hasher->hash(['foo' => 'bar']);

        $this->assertTrue($this->hasher->verify([
            'credentials' => [
                'hash' => $hash,
            ],
            'data' => [
                'foo' => 'bar',
            ],
        ]));
    }

    public function test_verifyHash_with_invalid_hash()
    {
        $hash = $this->hasher->hash(['foo' => 'bar']);

        $this->assertFalse($this->hasher->verify([
            'credentials' => [
                'hash' => $hash,
            ],
            'data' => [
                'bar' => 'foo',
            ],
        ]));
    }

    public function test_verifyHash_without_data()
    {
        $this->assertFalse($this->hasher->verify([]));
    }
}
