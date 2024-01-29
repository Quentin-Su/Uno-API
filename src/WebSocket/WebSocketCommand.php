<?php

namespace App\Command;

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\WebSocket\WebSocketServer;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WebSocketCommand extends Command
{
    /**
     * @var JWTTokenManagerInterface $jwtManager
     */
    private $jwtManager;

    /**
     * @var EntityManagerInterface $entityManager
     */
    private $entityManager;

    public function __construct(JWTTokenManagerInterface $jwtManager, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->jwtManager = $jwtManager;
        $this->entityManager = $entityManager;
    }

    protected static $defaultName = 'websocket:start';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new WebSocketServer($this->jwtManager, $this->entityManager)
                )
            ),
            8080
        );

        $output->writeln('WebSocket server started on port 8080');
        $server->run();
    }
}