<?php

namespace Waska\LaravelWithDBTransactions\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class WithDBTransactions
{
    /**
     * @param Request $request
     * @param Closure $next
     * @param int $attempts
     * @return mixed
     */
    public function handle($request, Closure $next, int $attempts = null)
    {
        if ($this->shouldIgnoreCurrentRoute() || $this->shouldIgnoreRequestMethod($request)) {
            return $next($request);
        }
        $attempts = $attempts ?: config('waska.with_db_transactions.maximum_attempts');
        do {
            DB::beginTransaction();
            if ($this->shouldCommitTransaction($response = $next($request))) {
                DB::commit();
                return $response;
            }
            DB::rollBack();
        } while (--$attempts > 0);
        return $response;
    }

    /**
     * @return bool
     */
    protected function shouldIgnoreCurrentRoute(): bool
    {
        return in_array(Route::current()->getName(), config('waska.with_db_transactions.ignore_route_names'));
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function shouldIgnoreRequestMethod($request): bool
    {
        foreach (config('waska.with_db_transactions.ignore_request_methods') as $method) {
            if ($request->isMethod($method)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $response
     * @return bool
     */
    protected function shouldCommitTransaction($response): bool
    {
        return $response instanceof Response && in_array($response->getStatusCode(), config('waska.with_db_transactions.commit_http_statuses'));
    }
}
