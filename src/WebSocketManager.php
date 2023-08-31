<?php

namespace Web\Sockets;

use Exception;
use Illuminate\Support\Collection;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class WebSocketManager implements MessageComponentInterface
{
    protected $connections = [];
    protected $currentClientId = 0;

    function onOpen(ConnectionInterface $conn, int $userId = null)
    {
        if ($userId) {
            $info['user_id'] = $userId;
            $info['data'] = $conn;
            array_push($this->connections, $info);
        } else {
            $this->currentClientId++;
            $clientId = $this->currentClientId;
            $info['user_id'] = $clientId;
            $info['data'] = $conn;
            array_push($this->connections, $info);
        }
        return $this->sendMessageToClient($conn, 'Bienvenido a la sala del chat usuario # '. json_encode($info));
    }

    function onMessage(ConnectionInterface $from, $msg)
    {
        $message = json_decode($msg, true);
        if (isset($message['action'])) {
            switch ($message['action']) {
                case 'chat':
                    return $this->broadcastMessage($from, $message, $message['to']);
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

    protected function broadcastMessage(ConnectionInterface $from, mixed $msg, int $userId = null)
    {
        $users = collect($this->connections);
        if (is_array($msg)) {
            return $this->broadcastMessageArray($msg, $users, $userId);
        } else {
            return $this->broadcastMessageSimple($from, $msg, $users);
        }
    }

    protected function closeConnection(ConnectionInterface $conn)
    {
        unset($this->connections[0]);
        $conn->close();
    }

    protected function broadcastMessageArray(array $msg, Collection $users, int $userId)
    {
        $data = json_encode([
            'action' => $msg['action'],
            'message' => $msg['message'],
            'to' => $msg['to'],
            'from' => $msg['from']
        ]);
        return $users->map(function ($user) use($userId, $data){
            if (($userId) && ($userId == $user['user_id'])) {
                $user['data']->send($data);
            }
            return $data;
        });
    }

    protected function broadcastMessageSimple(ConnectionInterface $from, string $msg, Collection $users)
    {
        $data = json_encode([
            'action' => 'info',
            'message' => $msg
        ]);
        return $users->map(function ($user) use($from, $data){
            if ($user['data'] !== $from) {
                $user['data']->send($data);
            }
            return $data;
        });
    }
}