<?php

namespace Codemash\Socket;

class Message {

    /**
     * The command used to trigger this message.
     *
     * @var string
     */
    public $command;


    /**
     * The inner data of the message.
     *
     * @var mixed
     */
    public $data;


    /**
     * Constructs a message.
     */
    public function __construct($data = null)
    {
        if ($data) {
            $data = json_decode($data, true);

            $this->command = $data['command'];
            $this->data = (object) array_get($data, 'data');
        }
    }


    /**
     * Serializes the message.
     *
     * @var string
     */
    public function serialize()
    {
        return json_encode([
            'command' => $this->command,
            'data' => $this->data
        ]);
    }

}
