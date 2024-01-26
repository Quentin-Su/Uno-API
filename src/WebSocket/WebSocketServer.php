<?php

namespace App\WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class WebSocketServer implements MessageComponentInterface {
    public function onOpen(ConnectionInterface $conn) {
        $jwt = $conn->httpRequest->getHeader('Authorization')[0] ?? null;        
    }

    public function onMessage(ConnectionInterface $from, $msg) {

    }

    public function onClose(ConnectionInterface $conn) {

    }

    public function onError(ConnectionInterface $conn, \Exception $e) {

    }
}