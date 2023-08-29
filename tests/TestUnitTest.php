<?php

namespace Tests;

use Tests\Mocks\WebSocketConnectionMock;
use Web\Sockets\WebSocketManager;

class TestUnitTest extends TestCase
{
    public function testWebSocketConnection()
    {
        $handler = new WebSocketManager();
        $connection = new WebSocketConnectionMock($handler);

        $connection->open();
        $message = $connection->send(json_encode(['action' => 'chat', 'message' => 'test de mensaje']));

        $this->assertEquals('Mensaje recibido: test de mensaje', $connection->getReceivedMessage());
        $connection->close();
    }
}