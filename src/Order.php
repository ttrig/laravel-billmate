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

    public function cancelled()
    {
        return $this->status === static::CANCELLED;
    }

    public function created()
    {
        return $this->status === static::CREATED;
    }

    public function failed()
    {
        return $this->status === static::FAILED;
    }

    public function paid()
    {
        return $this->status === static::PAID;
    }

    public function pending()
    {
        return $this->status === static::PENDING;
    }
}
