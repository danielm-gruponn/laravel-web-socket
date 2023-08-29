<?php

namespace Tests\Mocks;

use Ratchet\ConnectionInterface;
use Web\Sockets\WebSocketManager;

class WebSocketConnectionMock implements ConnectionInterface
{
    protected $handler;
    protected $receivedMessage;

    public function __construct(WebSocketManager $handler)
    {
        $this->handler = $handler;
    }

    public function open()
    {
        return $this->handler->onOpen($this);
    }

    public function send($data)
    {
        $this->receivedMessage = $data;
        return $this->handler->onMessage($this, $data);
    }

    public function close()
    {
        return $this->handler->onClose($this);
    }

    public function getReceivedMessage()
    {
        return 'Mensaje recibido: '.json_decode($this->receivedMessage)->message;
    }

}