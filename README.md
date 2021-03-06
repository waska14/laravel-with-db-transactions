## waska14/laravel-with-db-transactions
**Are you tired of writing database transactions for every controller method?**

*This package solves the problem with **middleware**.*

*Middleware begins database transaction and depending response (and **config** also) commits/rollbacks it.*

### Docs
* [Installation](#installation)
* [Configuration (Config based management)](#configuration)
* [Manual usage](#manual-usage)
* [Actions and events](#actions-and-events)

## Installation
Add the package in your composer.json by executing the command.

```bash
composer require waska14/laravel-with-db-transactions
```

For Laravel versions before 5.5 or if not using **auto-discovery**, register the service provider in `config/app.php`

```php
'providers' => [
    /*
     * Package Service Providers...
     */
    \Waska\LaravelWithDBTransactions\WithDBTransactionsServiceProvider::class,
],
```


## Configuration

If you want to change ***default configuration***, you must publish default configuration file to your project by running this command in console:
```bash
php artisan vendor:publish --tag=waska-with-db-transactions-config
```

This command will copy file `[/vendor/waska14/laravel-with-db-transactions/config/waska.with_db_transactions.php]` to `[/config/waska.with_db_transactions.php]`

Default `waska.with_db_transactions.php` looks like:
```php
return [
    /*
     * Route names that will be processed without transaction by WithDBTransactions middleware
     */
    'ignore_route_names' => [
        // login
    ],

    /*
     * Request methods that will be processed without transaction by WithDBTransactions middleware
     */
    'ignore_request_methods' => [
        'get',
    ],

    /*
     * Maximum attempts for transaction by default.
     * Note: You can ignore route name, than use middleware with_db_transactions:N where N is the number of attempts.
     */
    'maximum_attempts' => 1,

    /*
     * If response's http status code is any of those, the transaction will be committed.
     * Note: https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
     */
    'commit_http_statuses' => [
        200, // OK
        201, // Created
        202, // Accepted
        203, // Non-Authoritative Information
        204, // No Content
        205, // Reset Content
        206, // Partial Content
        207, // Multi-Status (WebDAV)
        208, // Already Reported (WebDAV)
        226, // IM Used

        300, // Multiple Choices
        301, // Moved Permanently
        302, // Found (Previously "Moved temporarily")
        303, // See Other (since HTTP/1.1)
        304, // Not Modified (RFC 7232)
        305, // Use Proxy (since HTTP/1.1)
        306, // Switch Proxy
        307, // Temporary Redirect (since HTTP/1.1)
        308, // Permanent Redirect (RFC 7538)
    ],

    /*
     * Middleware default class.
     * Anytime you can extend/override the class and change namespace of middleware_class
     */
    'middleware_class' => \Waska\LaravelWithDBTransactions\Http\Middleware\WithDBTransactions::class,

    /*
     * Default alias for middleware
     */
    'middleware_alias' => 'with_db_transactions',

    /*
     * List of middleware groups, where will be pushed with_db_transactions middleware by default (from ServiceProvider)
     */
    'middleware_groups' => [
//        'api',
//        'web',
    ],

    /*
     * Maximum attempts for middleware_groups
     * Note: if value is null, default maximum_attempts will be used.
     */
    'maximum_attempts_for_groups' => null,

    /*
     * Before and after action events
     */
    'before_begin_transaction_event' => \Waska\LaravelWithDBTransactions\Events\BeforeBeginTransactionEvent::class,
    'after_begin_transaction_event' => \Waska\LaravelWithDBTransactions\Events\AfterBeginTransactionEvent::class,
    'before_commit_event' => \Waska\LaravelWithDBTransactions\Events\BeforeCommitEvent::class,
    'after_commit_event' => \Waska\LaravelWithDBTransactions\Events\AfterCommitEvent::class,
    'before_rollback_event' => \Waska\LaravelWithDBTransactions\Events\BeforeRollbackEvent::class,
    'after_rollback_event' => \Waska\LaravelWithDBTransactions\Events\AfterRollbackEvent::class,
    'before_every_rollback_event' => \Waska\LaravelWithDBTransactions\Events\BeforeEveryRollbackEvent::class,
    'after_every_rollback_event' => \Waska\LaravelWithDBTransactions\Events\AfterEveryRollbackEvent::class,
];
```

You can manage route middleware globally (automatically) from config:

 Key  | Value(s) | Comment
:---------|:----------|:----------
middleware_groups |  Keys of `protected $middlewareGroups` from `app/Http/Kernel.php`. | Every route having the group middleware will be processed with database transaction.
ignore_request_methods | HTTP request methods names (GET/HEAD) | Those methods won'be processed with database transaction.
ignore_route_names | route names | Those methods also won't be processed with database transaction.
commit_http_statuses | HTTP status codes | If transaction has begun and the response has any of those statuses, the transaction will be committed.
maximum_attempts | `integer` | Maybe if deadlocks occurs or something else

If you want **manually management**, you can use `middleware_alias` (or change it, also) as route middleware.

## Manual usage
If you define `middleware_groups` as an empty array in config, none of the routes will be processed with database transactions by default, **until** you set manually the middleware for the specific **routes**/**route groups**.

**Note** that you can fill `middleware_groups` and also use middleware **manually** anywhere you want the method to be processed with database transaction.

Default middleware alias is `with_db_transactions` which can be changed in config, also.

**Note** that all configuration (`ignore_route_names`, `ignore_request_methods`, `maximum_attempts`, `commit_http_statuses` etc.) will also work when using this package manually.

## Actions and Events
After version `2.0.0` you can define closures that will be executed before/after commit/rollback(s).

There are 6 static functions that you can call with **`Waska\LaravelWithDBTransactions\Helpers\WithDBTransactions`** class:
```php
WithDBTransactions::beforeCommit(callable $closure); // Before committing database transaction
WithDBTransactions::afterCommit(callable $closure); // After committing database transaction
WithDBTransactions::beforeRollback(callable $closure); // Before last rollback
WithDBTransactions::afterRollback(callable $closure); // After last rollback
WithDBTransactions::beforeEveryRollback(callable $closure); // Before every (except last) rollback
WithDBTransactions::afterEveryRollback(callable $closure); // After every (except last) rollback
```

**Usage:**
```php
WithDBTransactions::beforeCommit(function () {
    \Log::info('First log and then commit!');
});
WithDBTransactions::afterCommit(function () {
    \Log::info('First commit and then log!');
});
```

Note that each function can be called as many times as you want. All closures will be executed with the same sequence.
E.x:
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Waska\LaravelWithDBTransactions\Helpers\WithDBTransactions;

class Controller extends BaseController
{
    public function create(Request $request)
    {
        // your stuff
        WithDBTransactions::beforeRollback(function () {
            \Log::info('log1 before rollback!');
        });
        WithDBTransactions::afterRollback(function () {
            \Log::info('log1 after rollback!');
        });
        WithDBTransactions::afterRollback(function () {
            \Log::info('log2 after rollback!');
        });
        // your stuff
        \App\User::create($request->validated());

        // Some error happened, so middleware rollbacks the transaction
        throw new \Exception('Something went wrong');
    }
}

```
After rollback `storage/logs/laravel.log` looks like:
```text
[Y-m-d H:i:s] local.INFO: log1 before rollback
[Y-m-d H:i:s] local.INFO: log1 after rollback
[Y-m-d H:i:s] local.INFO: log2 after rollback
```


After version `2.0.0` package dispatches **events** also.

You can listen them and do whatever you want in listener.

List of events:
- `\Waska\LaravelWithDBTransactions\Events\BeforeBeginTransactionEvent` Before begin transaction
- `\Waska\LaravelWithDBTransactions\Events\AfterBeginTransactionEvent` After begin transaction
- `\Waska\LaravelWithDBTransactions\Events\BeforeCommitEvent` Before commit
- `\Waska\LaravelWithDBTransactions\Events\AfterCommitEvent` After commit
- `\Waska\LaravelWithDBTransactions\Events\BeforeRollbackEvent` Before latest rollback
- `\Waska\LaravelWithDBTransactions\Events\AfterRollbackEvent` After latest rollback
- `\Waska\LaravelWithDBTransactions\Events\BeforeEveryRollbackEvent` Before every rollback
- `\Waska\LaravelWithDBTransactions\Events\AfterEveryRollbackEvent` After every rollback
