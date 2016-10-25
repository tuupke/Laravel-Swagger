<?php namespace Tuupke\Swagger;

use Illuminate\Support\ServiceProvider;

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
        $this->publishes([
            __DIR__.'/swagger.php' => config_path('swagger.php'),
        ]);
    }
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'swaggervel.php', 'swagger'
        );

        require_once __DIR__ .'/routes.php';
    }

}
