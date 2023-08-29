<?php

namespace Console;

use Illuminate\Console\Command;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Web\Sockets\WebSocketManager;

class StartWebSocketServer extends Command
{
    protected $signature = 'websocket:start';
    protected $description = 'inicio del servidor websocket';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('iniciando servidor de websockets....');

        $webSocket = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new WebSocketManager()
                )
            ),
            6001
        );
        $webSocket->run();
    }
}