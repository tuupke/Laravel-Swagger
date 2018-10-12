<?php namespace Tuupke\Swagger;

use \Illuminate\Support\ServiceProvider;
use Route;
use Config;

class SwaggerServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot() {
        $this->loadViewsFrom(__DIR__ . '/views', 'package-swagger');
        $this->publishes([__DIR__ . '/swagger.php' => config_path('swagger.php')]);
        $this->publishes([__DIR__ . '/../swagger-public' => public_path('vendor/swagger')], 'public');
        if ($this->app->runningInConsole())
            $this->commands([SwaggerConsole::class]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->mergeConfigFrom(__DIR__ . '/swagger.php', 'swagger');
        if (!$this->app->routesAreCached())
            Route::group(['prefix' => Config::get('swagger.ui-route')], function() {
                Route::get(Config::get('swagger.docs-route', 'spec')."/{environment?}/{page?}", '\Tuupke\Swagger\SwaggerController@docs');
                Route::get("{environment?}", '\Tuupke\Swagger\SwaggerController@ui');
            });
    }
}
