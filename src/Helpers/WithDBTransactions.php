<?php

namespace Waska\LaravelWithDBTransactions\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Waska\LaravelWithDBTransactions\Exceptions\InvalidCallableParameterException;
use Waska\LaravelWithDBTransactions\Exceptions\MiddlewareIsNotPassedException;

/**
 * @method static beforeCommit(callable $closure)
 * @method static afterCommit(callable $closure)
 * @method static beforeRollback(callable $closure)
 * @method static afterRollback(callable $closure)
 * @method static beforeEveryRollback(callable $closure)
 * @method static afterEveryRollback(callable $closure)
 */
class WithDBTransactions
{
    protected static $middlewareStarted = false;
    protected static $currentAttempt = 0;
    protected static $closures = [];

    /**
     * @param $name
     * @param $arguments
     * @return void
     *
     * @throws MiddlewareIsNotPassedException
     * @throws InvalidCallableParameterException
     */
    public static function __callStatic($name, $arguments)
    {
        if (!self::isMiddlewareStarted()) {
            throw new MiddlewareIsNotPassedException('You are not using WithDBTransactions middleware!');
        }
        $closure = Arr::first($arguments);
        if (!is_callable($closure)) {
            throw new InvalidCallableParameterException('You must pass callable as an argument!');
        }
        self::$closures[Str::snake($name)][] = $closure;
    }

    public static function startMiddleware()
    {
        self::$middlewareStarted = true;
    }

    public static function stopMiddleware()
    {
        self::$middlewareStarted = false;
    }

    public static function isMiddlewareStarted(): bool
    {
        return self::$middlewareStarted;
    }

    /**
     * Begin transaction and do before and after stuff.
     *
     * @param Request $request
     * @return void
     */
    public static function beginTransaction($request)
    {
        self::$closures = [];
        self::$currentAttempt++;
        self::event('before_begin_transaction_event', $request);
        DB::beginTransaction();
        self::event('after_begin_transaction_event', $request);
    }

    /**
     * Commit transaction and do before and after stuff.
     *
     * @param Request $request
     * @return void
     */
    public static function commit($request)
    {
        self::execute('before_commit');
        self::event('before_commit_event', $request);
        DB::commit();
        self::execute('after_commit');
        self::event('after_commit_event', $request);
    }

    /**
     * Rollback transaction and do before and after stuff.
     *
     * @param Request $request
     * @param bool $latest
     * @return void
     */
    public static function rollback($request, bool $latest = true)
    {
        $method = $latest ? 'rollback' : 'every_rollback';
        self::execute('before_' . $method);
        self::event('before_' . $method . '_event', $request);
        DB::rollBack();
        self::execute('after_' . $method);
        self::event('after_' . $method . '_event', $request);
    }

    /**
     * @param string $name
     * @return void
     */
    protected static function execute(string $name)
    {
        if (!empty(self::$closures[$name])) {
            foreach (self::$closures[$name] as $callable) {
                $callable();
            }
        }
    }

    /**
     * This function dispatches an event depending event $key
     *
     * @param string $key
     * @param $request
     */
    protected static function event(string $key, $request)
    {
        $class = config('waska.with_db_transactions.' . $key);
        event(new $class($request, self::$currentAttempt));
    }
}
