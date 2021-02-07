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
use Smart\Gii\Services\ConfigService;

class GiiServiceProvider extends ServiceProvider
{
    protected $namespace = 'Smart\Gii\Http\Controllers';

    /**
     * Bootstrap any package services.
     */
    public function boot()
    {
        if (!ConfigService::enabled()) {
            return;
        }

        $this->registerRoutes();
        $this->registerPublishing();

        $this->loadViewsFrom(
            __DIR__ . '/../../resources/views',
            'gii'
        );
    }

    public function register()
    {

        $this->mergeConfigFrom($this->configFile(), ConfigService::key());

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
                __DIR__ . '/../../public' => public_path('vendor/smart'),
            ], 'gii-assets');

            $this->publishes([
                $this->configFile() => config_path('smart-gii.php'),
            ], 'gii-config');
        }
    }

    /**
     * Register the package routes.
     */
    private function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom($this->routeFile());
        });
    }

    /**
     * @return string
     */
    private function configFile()
    {
        return __DIR__ . '/../../config/smart-gii.php';
    }

    /**
     * @return string
     */
    private function routeFile()
    {
        return __DIR__ . '/../../routes/smart-gii.php';
    }

    /**
     * Get the doc route group configuration array.
     *
     * @return array
     */
    private function routeConfiguration()
    {
        return [
            'domain' => ConfigService::domain(),
            'namespace' => 'Smart\Gii\Http\Controllers',
            'prefix' => ConfigService::prefix(),
            'middleware' => config('middleware'),
        ];
    }
}
