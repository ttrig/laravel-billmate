<?php

namespace Ttrig\Billmate;

use Illuminate\Support\Fluent;

class Order extends Fluent
{
    const CANCELLED = 'Cancelled';
    const CREATED = 'Created';
    const FAILED = 'Failed';
    const PAID = 'Paid';
    const PENDING = 'Pending';

    const INVOICE = 1;
    const INVOICE_SERVICE = 2;
    const PART_PAYMENT = 4;
    const CARD = 8;
    const BANK = 16;
    const CASH = 32;

    public function isCancelled()
    {
        return $this->status === static::CANCELLED;
    }

    public function isCreated()
    {
        return $this->status === static::CREATED;
    }

    public function isFailed()
    {
        return $this->status === static::FAILED;
    }

    public function isPaid()
    {
        return $this->status === static::PAID;
    }

    public function isPending()
    {
        return $this->status === static::PENDING;
    }
}
