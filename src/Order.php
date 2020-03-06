<?php

namespace Ttrig\Billmate;

use Illuminate\Support\Fluent;

class Order extends Fluent
{
    public const CANCELLED = 'Cancelled';
    public const CREATED = 'Created';
    public const FAILED = 'Failed';
    public const PAID = 'Paid';
    public const PENDING = 'Pending';

    public const INVOICE = 1;
    public const INVOICE_SERVICE = 2;
    public const PART_PAYMENT = 4;
    public const CARD = 8;
    public const BANK = 16;
    public const CASH = 32;

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
