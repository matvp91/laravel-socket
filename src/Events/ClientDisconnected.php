<?php

namespace Codemash\Socket\Events;

class ClientDisconnected extends Event {

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($server, $clients, $client)
    {
        parent::__construct($server, $clients, $client);
    }

}
