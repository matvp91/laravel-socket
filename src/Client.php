<?php

namespace Codemash\Socket;

use Codemash\Socket\Message;
use Exception;
use Auth;
use Config;

class Client {

    public $id;

    /**
     * The internal data storage.
     *
     * @var array
     */
    private $data = [];


    /**
     * The connection interface.
     *
     * @var Socket\ConnectionInterface
     */
    public $connection;


    /**
     * The IP of the client.
     *
     * @var string
     */
    public $ip;


    /**
     * The session manager.
     *
     * @var Codemash\Socket\SessionManager
     */
    public $session_manager;


    /**
     * The user.
     *
     * @var mixed
     */
    private $user = null;


    /**
     * Constructs a client.
     */
    public function __construct($connection, $session_manager)
    {
        $this->connection = $connection;

        $this->session_manager = $session_manager;

        $this->ip = $connection->remoteAddress;

        // Check if client is behind a proxy.
        if (!$this->ip) {
            $this->ip = $connection->WebSocket->request->getHeader('X-Forwarded-For');
        }

        // Handle session.
        $this->session_manager->handle();

        $this->id = $connection->resourceId;
    }


    /**
     * Sets a data attribute.
     *
     * @return void
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }


    /**
     * Checks whether an attribute is set or not.
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }


    /**
     * Gets a data attribute.
     *
     * @return mixed
     */
    public function __get($name)
    {
        $identifier = 'get' . camel_case($name) . 'Attribute';

        if (method_exists($this, $identifier)) {
            return call_user_func([$this, $identifier]);
        }

        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        throw new Exception('Undefined property via __get(): ' . $name);
    }


    /**
     * Sends a message to this client.
     *
     * @return void
     */
    public function send($command, $data)
    {
        $message = new Message;

        $message->command = $command;
        $message->data = $data;

        $this->connection->send($message->serialize());
    }


    /**
     * Hooks in the user getter.
     *
     * @return mixed
     */
    public function getUser() {
        $session = $this->session_manager->session;

        if ($session && !$this->user) {
            $user_id = $session->get(Auth::getName());

            if ($user_id && is_null($this->user)) {
                $user_model = Config::get('auth.providers.users.model');

                if ($user_model) {
                    $this->user = $user_model::find($user_id);
                }
            } else {
                $this->user = null;
            }
        }

        return $this->user;
    }


    /**
     * Check if user is authenticated.
     *
     * @return bool
     */
    public function authed() {
        $this->getUser();

        return !is_null($this->user);
    }

}
