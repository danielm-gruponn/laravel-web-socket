<?php

namespace Web\Sockets;

use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class WebSocketManager implements MessageComponentInterface
{
    protected $connections = [];

    function onOpen(ConnectionInterface $conn)
    {
        $this->connections[] = $conn;
        return $this->sendMessageToClient($conn, 'Bienvenido a la sala del chat.');
    }

    function onMessage(ConnectionInterface $from, $msg)
    {
        $message = json_decode($msg, true);
        if (isset($message['action'])) {
            switch ($message['action']) {
                case 'chat':
                    return $this->broadcastMessage($from, $message['message']);
                    break;
                default:
                    return 'error';
                    break;
            }
        }
    }

    function onClose(ConnectionInterface $conn)
    {
        $this->connections = array_filter($this->connections, function ($connection) use ($conn) {
            return $connection !== $conn;
        });
        return $this->broadcastMessage($conn, 'Un cliente se ha desconectado');
    }

    function onError(ConnectionInterface $conn, Exception $e)
    {
        $this->closeConnection($conn);
        $errorMessage = 'Ocurrio un error en la conexion: '.$e->getMessage();
        $this->broadcastMessage($conn, $errorMessage);
    }

    protected function sendMessageToClient(ConnectionInterface $conn, $message)
    {
        $data = json_encode(['message' => $message]);
        try {
            $conn->send($data);
            return $data;
        } catch (Exception $e) {
            return "error: ".$e;
        }
    }

    protected function broadcastMessage(ConnectionInterface $from, mixed $msg)
    {
        $data = json_encode([
            'action' => 'chat',
            'message' => $msg
        ]);
        foreach ($this->connections as $connection) {
            if ($connection !== $from) {
                return $from->send($data);
            }
            return $data;
        }
    }

    protected function closeConnection(ConnectionInterface $conn)
    {
        unset($this->connections[0]);
        $conn->close();
    }
}