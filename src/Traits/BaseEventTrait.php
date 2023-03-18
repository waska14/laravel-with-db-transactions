<?php

namespace Waska\LaravelWithDBTransactions\Traits;

use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

trait BaseEventTrait
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $request;
    protected $currentAttempt;

    /**
     * Create a new event instance.
     *
     * @param Request|null $request
     * @param int $currentAttempt
     */
    public function __construct(Request $request = null, int $currentAttempt = 1)
    {
        $this->request = $request;
        $this->currentAttempt = $currentAttempt;
    }

    /**
     * @return Request|null
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return int
     */
    public function getCurrentAttempt(): int
    {
        return $this->currentAttempt;
    }
}
