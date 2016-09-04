<?php

namespace Codemash\Socket;

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Config;

class Server {

    /**
     * The IO interface.
     *
     * @var IoServer
     */
    private $io;


    /**
     * The server port.
     *
     * @var int
     */
    private $port;


    /**
     * The command used to fire the server.
     *
     * @var Illuminate\Console\Command
     */
    private $command;


    /**
     * Constructs a server.
     */
    public function __construct($command, $port = null, MessageComponent $message_component = null)
    {
        $this->command = $command;

        if (is_null($port)) {
            $port = Config::get('socket.default_port');
        }

        if (is_null($message_component)) {
            $message_component = new MessageComponent($this);
        }

        $this->port = $port;

        $http_server = new HttpServer(new WsServer($message_component));
        $this->io = IoServer::factory($http_server, $port);
    }


    /**
     * Run the server.
     *
     * @return void
     */
    public function run()
    {
        $this->io->run();
    }


    /**
     * Gets the IO interface.
     *
     * @return IoServer
     */
    public function getIO()
    {
        return $this->io;
    }


    /**
     * Gets the port.
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }


    /**
     * Constructs and gets the endpoint URL.
     *
     * @return string
     */
    public function getUrl($include_scheme = true)
    {
        $parts = parse_url(url('/'));
        $scheme = '';
        if ($include_scheme) {
            $scheme = 'ws://';
        }
        return $scheme . $parts['host'] . ':' . $this->port;
    }


    /**
     * Gets the command used to trigger this server.
     *
     * @return Illuminate\Console\Command
     */
    public function getCommand()
    {
        return $this->command;
    }

}
