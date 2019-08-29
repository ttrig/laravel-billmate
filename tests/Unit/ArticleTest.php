<?php

namespace Ttrig\Billate\Tests\Unit;

use Ttrig\Billmate\Article;
use Ttrig\Billmate\Tests\TestCase;

class ArticleTest extends TestCase
{
    private $article;

    protected function setUp(): void
    {
        parent::setUp();

        $this->article = new Article([
            'number' => 1000,
            'title' => 'Potatoes',
            'price' => 49,
            'taxrate' => 0.25,
        ]);
    }

    public function test_getters()
    {
        $this->assertEquals(4900, $this->article->price());
        $this->assertEquals(25, $this->article->taxrate());
        $this->assertEquals(1, $this->article->quantity());
        $this->assertEquals(1225, $this->article->totalTax());
        $this->assertEquals(6125, $this->article->totalWithTax());
        $this->assertEquals(4900, $this->article->totalWithoutTax());
    }

    public function test_paymentData()
    {
        $this->assertEquals([
            'quantity' => 1,
            'taxrate' => 25,
            'discount' => 0,
            'artnr' => 1000,
            'title' => 'Potatoes',
            'aprice' => 4900,
            'withouttax' => 4900,
        ], $this->article->paymentData());
    }
}
