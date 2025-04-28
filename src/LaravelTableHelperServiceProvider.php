<?php

namespace Plokko\LaravelTableHelper;

use Illuminate\Support\ServiceProvider;

class LaravelTableHelperServiceProvider extends ServiceProvider
{
    const PackageName = 'laravel-tablehelper';

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //-- Publish config file --//
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path(self::PackageName . '.php'),
        ], 'config');

    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        /// Merge default config ///
        $this->mergeConfigFrom(
            __DIR__ . '/../config/config.php',
            self::PackageName
        );

        $this->app->bind(TableHelperAccessor::class, function ($app) {
            return new TableHelperAccessor($app->config->get(self::PackageName, []));
        });
    }

    public function provides(): array
    {
        return [
            TableHelperAccessor::class,
        ];
    }
}
