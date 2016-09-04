<?php

namespace Codemash\Socket;

use Illuminate\Support\Collection;

class Clients extends Collection {

    /**
     * Find a client by their connection.
     *
     * @return Codemash\Socket\Client|null
     */
    public function findByConnection($connection)
    {
        return $this->first(function ($key, $client) use ($connection) {
            return $client->connection === $connection;
        });
    }

    /**
     * Adds a client connection.
     */
    public function add($client) {
        $this->items[$client->id] = $client;
    }

    /**
     * Removes a client connection.
     */
    public function remove($client) {
        $this->offsetUnset($client->id);
    }

    /**
     * Gets only the authed users.
     */
    public function authedClients()
    {
        return $this->filter(function ($client) {
            return $client->authed();
        });
    }

}
