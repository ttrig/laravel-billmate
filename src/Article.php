<?php

namespace Ttrig\Billmate;

use Illuminate\Support\Fluent;

class Article extends Fluent
{
    public function price(): int
    {
        return $this->price ? $this->price * 100 : 0;
    }

    public function taxrate(): int
    {
        return $this->taxrate ? round($this->taxrate * 100) : 0;
    }

    public function quantity(): int
    {
        return $this->quantity ?: 1;
    }

    public function totalTax(): int
    {
        return $this->totalWithoutTax() * ($this->taxrate() / 100);
    }

    public function totalWithTax(): int
    {
        return $this->totalWithoutTax() + $this->totalTax();
    }

    public function totalWithoutTax(): int
    {
        return $this->price() * $this->quantity();
    }

    public function paymentData(): array
    {
        return [
            'artnr' => $this->number ?: $this->artnr,
            'title' => $this->title,
            'quantity' => $this->quantity(),
            'aprice' => $this->price(),
            'taxrate' => $this->taxrate(),
            'withouttax' => $this->totalWithoutTax(),
            'discount' => 0,
        ];
    }
}
