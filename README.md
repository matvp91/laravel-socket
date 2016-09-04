# Laravel Socket

This package allows you to use sockets easily and elegantly in your Laravel 5 application. Based on the awesome PHP socket library, [Ratchet](https://github.com/ratchetphp/Ratchet). Read the instructions below to get setup.

## Requirements

Laravel 5.x.

## Installation


You can install the package using the [Composer](https://getcomposer.org/) package manager. You can install it by running this command in your project root:

```sh
composer require codemash/socket
```

Add the `Codemash\Socket\SocketServiceProvider` provider to the `providers` array in `config/app.php`':

```php
'providers' => [
    ...
    Codemash\Socket\SocketServiceProvider::class,
],
```

Then, add the facade to your `aliases` array. The default facade provides an easy-to-use interface to integrate the socket files in your view.

```php
'aliases' => [
    ...
    'Socket' => Codemash\Socket\Facades\Socket::class,
]
```

Finally, the config and the javascript files need to be published, which can be done by running the following command:

```sh
php artisan vendor:publish --provider="Codemash\Socket\SocketServiceProvider"
```

The published assets can be found at `config/socket.php` and the default javascript at `public/vendor/socket/socket.js`. It is important to know that the `Socket::javascript()` facade function will include both a default socket located at `window.appSocket` and the `socket.js` source file located in the vendor folder. These are merely a start, and provide a quick way to work with the sockets but you are always free to write a custom implementation.

## Getting started

Let's create a simple application that sends a message to all other connected clients. When a socket action occurs, it will be wrapped around a Laravel event and triggered. This is a great way for us to catch these events and act upon them. Let's register our listener in the `app/Providers/EventServiceProvider.php` file like this:

```php
<?php

namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
   ...

    /**
    * The subscriber classes to register.
    *
    * @var array
    */
    protected $subscribe = [
        'App\Listeners\MessageEventListener'
    ];
}
```

And create the listener at the following path: `app/Listeners/MessageEventListener.php`. Listeners provide 3 basic events. For our example here, we'll only be using the `onMessageReceived` event.

```php
<?php

namespace App\Listeners;

use Codemash\Socket\Events\MessageReceived;
use Codemash\Socket\Events\ClientConnected;
use Codemash\Socket\Events\ClientDisconnected;

class MessageEventListener {

    public function onMessageReceived(MessageReceived $event)
    {
        $message = $event->message;

        // If the incomming command is 'sendMessageToOthers', forward the message to the others.
        if ($message->command === 'sendMessageToOthers') {
            // To get the client sending this message, use the $event->from property.
            // To get a list of all connected clients, use the $event->clients pointer.
            $others = $event->allOtherClients();
            foreach ($others as $client) {
                // The $message->data property holds the actual message
                $client->send('newMessage', $message->data);
            }
        }
    }

    public function onConnected(ClientConnected $event)
    {
        // Not used in this example.
    }

    public function onDisconnected(ClientDisconnected $event)
    {
        // Not used in this example.
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Codemash\Socket\Events\ClientConnected',
            'App\Listeners\MessageEventListener@onConnected'
        );

        $events->listen(
            'Codemash\Socket\Events\MessageReceived',
            'App\Listeners\MessageEventListener@onMessageReceived'
        );

        $events->listen(
            'Codemash\Socket\Events\ClientDisconnected',
            'App\Listeners\MessageEventListener@onDisconnected'
        );

    }
}
```

What the application above does, is the following: a connected client sends a message with the `sendMessageToOthers` command, which basically forwards the message to the rest of the connected clients on your application. It is **important** to note that a client is not the same as a `User` model. A client is simply a connection from someones browser to your Laravel application, no matter if that user is authed or not. There is a possibility to fetch the connected authentication model, more on that later.

Now it's time to write the client side, luckily the `Socket` facade takes care of that in no time. Create a blade template with the following content:

```html
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <button onclick="sendMessage()">Send message</button>
        {!! Socket::javascript() !!}
        <script>
            var socket = window.appSocket;

            function sendMessage() {
                var text = window.prompt('Which message would you like to send?');
                socket.send('sendMessageToOthers', text);
            }

            socket.on('newMessage', function (newMessage) {
                alert('New message: ' + newMessage);
            });

            socket.connect(function () {
                // The socket is connected.
            });
        </script>
    </body>
</html>
```

Finally, let's run the socket listener. You can do this by running the following artisan command in the project root:

```sh
php artisan socket:listen
```

## Using Eloquent models

Laravel Socket reads the session when available and maps the `User` eloquent model to your client. You can then retrieve the Eloquent model by using the following code:

```php
foreach ($clients as $client) {
    if ($client->authed()) {
        $user = $client->getUser();
        // $user now holds the App\User model,
        // or the model set in the 'config.auth.providers.users.model' config variable.
    }
}
```

Whenever you're using the clients list, like `$event->clients`, this is a Laravel Collection object. Methods such as filter, map, and so on, work very well on it.

## Production

Ubuntu provides the neat `nohup` tool, which runs processes on the background. In case you'd like to run your socket on a production server and you're on Ubuntu, you may always use the nohup tool to run the socket listener.

```sh
nohup php artisan socket:listen &
```

When using the `jobs` command, you'll see the socket running. It's easy to kill the process using the `kill <pid>` command. The process ID is listed in the jobs list.

## Contributing

If you're having problems, spot a bug, or have a feature suggestion, please log and issue on Github. If you'd like to have a crack yourself, fork the package and make a pull request. Any improvements are more than welcome.

