<?php

namespace App\WebSocket;

use SplObjectStorage;
use Ratchet\ConnectionInterface;

class WebSocketManager {
    private $clients;

    public function __construct()
    {
        // The controller re-executes this, and it's problematic.
        $this->clients = new SplObjectStorage();
    }

    public function addConnection(ConnectionInterface $connection)
    {
        $this->clients->attach($connection);
    }

    public function removeConnection(ConnectionInterface $connection)
    {
        $this->clients->detach($connection);
    }

    public function sendToUser()
    {
        dump($this->clients);
    }
}