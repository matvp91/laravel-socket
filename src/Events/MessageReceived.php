<?php

namespace Codemash\Socket\Events;

class MessageReceived extends Event {

    /**
     * The owner of the message.
     *
     * @var Codemash\Socket\Client
     */
    public $from;


    /**
     * The message.
     *
     * @var Codemash\Socket\Message
     */
    public $message;


    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($server, $clients, $client, $message)
    {
        parent::__construct($server, $clients, $client);

        $this->from = $client;

        $this->message = $message;
    }

}
