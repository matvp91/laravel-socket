<?php

namespace Codemash\Socket;

use Illuminate\Session\SessionManager as SessionHandler;
use App;
use Config;
use Crypt;
use Exception;

class SessionManager {

    /**
     * The connection interface.
     *
     * @var Ratchet\ConnectionInterface
     */
    private $connection;


    /**
     * The session.
     *
     * @var mixed
     */
    public $session = null;


    /**
     * Constructs a session component.
     */
    public function __construct($connection)
    {
        $this->connection = $connection;

        $app = App::getInstance();
        $session_manager = new SessionHandler($app);
        $this->session = $session_manager->driver();
    }


    /**
     * Gets a cookie by the name and if needed, can decrypt the value.
     *
     * @return string|null
     */
    private function getCookie($name, $decrypt = false)
    {
        try {
            $cookies = $this->connection->WebSocket->request->getCookies();

            if (isset($cookies[$name])) {
                $raw = rawurldecode($cookies[$name]);

                if (!$decrypt) {
                    return $raw;
                }

                $decrypted = @Crypt::decrypt($raw);

                if ($decrypted) {
                    return $decrypted;
                }
            }

            return null;
        } catch (Exception $ex) {
            \Log::error($ex);

            return null;
        }
    }


    /**
     * Resolves the laravel session and sets the session.
     *
     * @return mixed
     */
    public function resolveSession()
    {
        $cookie_name = Config::get('session.cookie');
        $session_id = $this->getCookie($cookie_name, true);

        if (!$session_id) {
            return null;
        }

        $this->session->setId($session_id);

        return $this->session;
    }


    /**
     * Checks if we have a session set.
     *
     * @return bool
     */
    public function hasSession()
    {
        return !is_null($this->session);
    }


    /**
     * Starts the session.
     *
     * @return void
     */
    public function start()
    {
        $this->session->start();
    }


    /**
     * Handles the session.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->hasSession()) {
            $this->start();
        }
    }

}
