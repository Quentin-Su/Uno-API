<?php

namespace App\WebSocket;

use App\WebSocket\WebSocketManager;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class WebSocketServer implements MessageComponentInterface
{
    protected $clients;

    public function __construct(WebSocketManager $webSocketManager)
    {
        $this->clients = $webSocketManager;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->addConnection($conn);
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->removeConnection($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $conn->close();
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {

    }
}