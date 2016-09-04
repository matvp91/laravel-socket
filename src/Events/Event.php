<?php

namespace Codemash\Socket\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class Event {
    use SerializesModels;


    /**
     * The port where the message is sent on.
     * Can be used if multiple servers are running.
     *
     * @var int
     */
    public $port;


    /**
     * All clients.
     *
     * @var array
     */
    public $clients;

    /**
     * The owner of the event.
     *
     * @var Codemash\Socket\Client
     */
    public $client;

    /**
     * Constructs an event.
     */
    public function __construct($server, $clients, $client)
    {
        $this->port = $server->getPort();

        $this->clients = $clients;

        $this->client = $client;
    }

    /**
     * Get all users connected.
     *
     * @var array
     */
    public function allOtherClients()
    {
        $current_client = $this->client;

        $filter = function ($client) use ($current_client) {
            return $client !== $current_client;
        };

        return $this->clients->filter($filter);
    }

}
