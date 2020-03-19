<?php

namespace Waska\LaravelWithDBTransactions\Events;

use Waska\LaravelWithDBTransactions\Traits\BaseEventTrait;

class AfterRollbackEvent
{
    use BaseEventTrait;
}
