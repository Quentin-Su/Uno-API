<?php

namespace App\Command;

use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\WebSocket\WebSocketServer;
use Ratchet\Server\IoServer;

class WebSocketCommand extends Command
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    protected function configure()
    {
        $this
            ->setName('websocket:start')
            ->setDescription('Starts the WebSocket server');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $webSocketManager = $this->container->get('app.websocket_manager');

        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new WebSocketServer($webSocketManager)
                )
            ),
            8080
        );

        $output->writeln('WebSocket server started on port 8080');
        $server->run();

        return Command::SUCCESS;
    }
}