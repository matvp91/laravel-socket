<?php

namespace Codemash\Socket;

use Config;

class Socket {

    /**
     * Includes the javascript file and initializes the Socket javascript object.
     *
     * @return string
     */
    public function javascript($url = null)
    {
         if (!$url) {
            $parts = parse_url(url('/'));
            if (!isset($parts['port'])) {
                $parts['port'] = Config::get('socket.default_port');
            }
            $url = 'ws://' . $parts['host'] . ':' . $parts['port'];
        }

        return implode('', [
            '<script src="' . url('vendor/socket/socket.js') .'"></script>',
            '<script>window.appSocket = new Socket("' . $url . '");</script>'
        ]);
    }

}
