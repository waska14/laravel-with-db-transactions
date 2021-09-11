<?php

/** @noinspection PhpFullyQualifiedNameUsageInspection */
return [
    /*
     * Route names that will be ignored by WithDBTransactions middleware
     */
    'ignore_route_names' => [
        // login
    ],

    /*
     * Request methods that will be ignored by WithDBTransactions middleware
     */
    'ignore_request_methods' => [
        'get',
    ],

    /*
     * Maximum attempts for transaction by default.
     * Note: If you want to override for a single route:
     * 1. Give it an unique route name
     * 2. Ignore the name in "ignore_route_names" array (above in this config file)
     * 3. Give to your route middleware: "with_db_transactions:N" where N is the number of attempts
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
     * Default middleware class.
     */
    'middleware_class' => \Waska\LaravelWithDBTransactions\Http\Middleware\WithDBTransactions::class,

    /*
     * Default alias for middleware
     */
    'middleware_alias' => 'with_db_transactions',

    /*
     * List of middleware groups, which will contain with_db_transactions middleware by default
     */
    'middleware_groups' => [
       // 'api',
       // 'web',
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
