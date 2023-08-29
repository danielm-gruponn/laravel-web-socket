<?php

namespace Web\Sockets;

use Illuminate\Support\ServiceProvider;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;


class WebSocketServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('command.websocket.start', function ($app) {
            // return new 
        });
    }
}