<?php

namespace Waska\LaravelWithDBTransactions;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class WithDBTransactionsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadDefaultConfig();
        $this->publishConfig();
        $this->defineMiddleware();
    }

    /**
     * Load default config.
     *
     * @return void
     */
    protected function loadDefaultConfig()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/waska.with_db_transactions.php', 'waska.with_db_transactions');
    }

    /**
     * Publish config. [Command: php artisan vendor:publish --tag=waska-with-db-transactions-config]
     *
     * @return void
     */
    protected function publishConfig()
    {
        $this->publishes([__DIR__ . '/../config/waska.with_db_transactions.php' => config_path('waska.with_db_transactions.php')], 'waska-with-db-transactions-config');
    }

    /**
     * This method defines alias of middleware in router.
     * Also it pushes the middleware to group (depending configuration).
     *
     * @return void
     */
    protected function defineMiddleware()
    {
        /** @var Router $router */
        $router = $this->app['router'];
        $router->aliasMiddleware(config('waska.with_db_transactions.middleware_alias'), config('waska.with_db_transactions.middleware_class'));

        foreach (config('waska.with_db_transactions.middleware_groups') as $group) {
            $router->pushMiddlewareToGroup($group, sprintf('%s:%s', config('waska.with_db_transactions.middleware_alias'), config('waska.with_db_transactions.maximum_attempts_for_groups') ?: config('waska.with_db_transactions.maximum_attempts')));
        }
    }
}
