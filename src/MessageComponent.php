<?php

namespace Codemash\Socket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Codemash\Socket\Events\MessageReceived;
use Codemash\Socket\Events\ClientConnected;
use Codemash\Socket\Events\ClientDisconnected;
use Exception;
use Event;

class MessageComponent implements MessageComponentInterface {

    /**
     * The command used to fire the server.
     *
     * @var Illuminate\Console\Command
     */
    protected $command;


    /**
     * The server.
     *
     * @var Codemash\Socket\Server
     */
    private $server;


    /**
     * Constructs the message handler.
     */
    public function __construct(Server $server)
    {
        $this->clients = new Clients;

        $this->command = $server->getCommand();

        $this->server = $server;
    }


    /**
     * Client connected.
     */
    public function onOpen(ConnectionInterface $connection)
    {
        // Resolve the session upon connection.
        $session_manager = new SessionManager($connection);
        $session_manager->resolveSession();

        // Create the client and add it.
        $client = new Client($connection, $session_manager);
        $this->clients->add($client);

        $this->command->info('Client connected');

        // Fire the event.
        Event::fire(new ClientConnected($this->server, $this->clients, $client));
    }


    /**
     * Incoming message.
     */
    public function onMessage(ConnectionInterface $from, $message)
    {
        $incoming_client = $this->clients->findByConnection($from);

        if ($incoming_client) {
            // Handle session.
            // $incoming_client->session_manager->handle();

            // Handle message.
            $message = new Message($message);

            // Fire the event.
            Event::fire(new MessageReceived($this->server, $this->clients, $incoming_client, $message));
        }
    }


    /**
     * Client disconnected.
     */
    public function onClose(ConnectionInterface $connection)
    {
        $client = $this->clients->findByConnection($connection);

        if ($client) {
            $this->clients->remove($client);

            $this->command->info('Client disconnected');

            // Fire the event.
            Event::fire(new ClientDisconnected($this->server, $this->clients, $client));
        }
    }


    /**
     * Socket error.
     */
    public function onError(ConnectionInterface $connection, Exception $e)
    {
        $this->command->error($e->getMessage());

        \Log::error($e);

        $connection->close();
    }

}
