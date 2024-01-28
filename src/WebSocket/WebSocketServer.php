<?php

namespace App\WebSocket;

use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class WebSocketServer implements MessageComponentInterface
{
    /**
     * @var JWTTokenManagerInterface $jwtManager
     */
    private $jwtManager;

    /**
     * @var EntityManagerInterface $entityManager
     */
    private $entityManager;

    /**
     * @var \SplObjectStorage $clients
     */
    protected $clients;

    public function __construct(JWTTokenManagerInterface $jwtManager, EntityManagerInterface $entityManager)
    {
        $this->jwtManager = $jwtManager;
        $this->entityManager = $entityManager;
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $jwtToken = $conn->httpRequest->getHeader('Authorization')[0] ?? null;

        if ($jwtToken) {
            try {
                $tokenData = $this->jwtManager->parse($jwtToken);

                $username = $tokenData['username'];
                $user = $this->entityManager->getRepository('App\Entity\User')->findOneBy(['username' => $username]);

                if ($user) {
                    $this->clients->attach(new UserIdObject($user->getId()), $conn);

                    dump($this->clients);
                }
                else {
                    return $conn->close(1011, 'User not found');
                }
            } 
            catch (\Exception $e) {
                return $conn->close(4001, 'Invalid JWT Token');
            };
        } 
        else {
            return $conn->close(4000, 'JWT Token missing');
        }
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {

    }

    public function onClose(ConnectionInterface $conn)
    {
        foreach ($this->clients as $user) {
            if ($this->clients->offsetGet($user) === $conn) {
                $this->clients->detach($user);
                break;
            }
        }

        dump($this->clients);
        return $conn->close(1000);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        
    }
}

class UserIdObject 
{
    private $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function getId()
    {
        return $this->userId;
    }
}