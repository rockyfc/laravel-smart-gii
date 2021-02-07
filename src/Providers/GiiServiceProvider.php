<?php

namespace Smart\Gii\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Smart\Gii\Console\ControllerCreator;
use Smart\Gii\Console\InstallCommand;
use Smart\Gii\Console\ModelCreator;
use Smart\Gii\Console\RepositoryCreator;
use Smart\Gii\Console\RequestFormCreator;
use Smart\Gii\Console\ResourceCreator;
use Smart\Gii\Console\SdkCreator;
use Smart\Gii\Console\SdkRestCreator;

class GiiServiceProvider extends ServiceProvider
{
    protected $namespace = 'Smart\Gii\Http\Controllers';

    /**
     * Bootstrap any package services.
     */
    public function boot()
    {
        if (!config('gii.enabled')) {
            return;
        }

        //Route::middlewareGroup('doc', config('doc.middleware', []));

        $this->registerRoutes();
        $this->registerPublishing();

        $this->loadViewsFrom(
            __DIR__ . '/../../resources/views',
            'gii'
        );
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/gii.php',
            'gii'
        );

        $this->commands([
            InstallCommand::class,
            ModelCreator::class,
            RequestFormCreator::class,
            ControllerCreator::class,
            ResourceCreator::class,
            RepositoryCreator::class,
            SdkCreator::class,
            SdkRestCreator::class,
        ]);
    }

    /**
     * 注册可以作为发布的包
     */
    private function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../public' => public_path('vendor/gii'),
            ], 'gii-assets');

            $this->publishes([
                __DIR__ . '/../../config/gii.php' => config_path('gii.php'),
            ], 'gii-config');
        }
    }

    /**
     * Register the package routes.
     */
    private function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/gii.php');
        });
    }

    /**
     * Get the doc route group configuration array.
     *
     * @return array
     */
    private function routeConfiguration()
    {
        return [
            'domain' => config('gii.domain', null),
            'namespace' => 'Smart\Gii\Http\Controllers',
            'prefix' => config('gii.prefix'),
            'middleware' => config('middleware'),
        ];
    }
}
