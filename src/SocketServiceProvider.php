<?php

namespace Codemash\Socket;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

class SocketServiceProvider extends ServiceProvider {

    /**
     * Register commands here.
     */
    protected $commands = [
        'Codemash\Socket\Commands\Listen',
    ];


    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/socket.php' => config_path('socket.php')
        ]);

        $this->publishes([
            __DIR__ . '/../assets/Socket.js' => public_path('vendor/socket/socket.js')
        ], 'public');
    }


    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands($this->commands);

        $this->app->bind('codemash.socket', function () {
            return new Socket;
        });
    }


    /**
     * Provides bindings to the application.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

}
