<?php

namespace Codemash\Socket\Commands;

use Illuminate\Console\Command;
use Codemash\Socket\Server;

class Listen extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socket:listen {--port=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle the servers incoming messages.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $port = $this->option('port');

        if ($port && !is_numeric($port)) {
            $this->error('Port must be numeric');
            return;
        }

        $server = new Server($this, $port);

        $this->info('Running on ' . $server->getUrl(false));

        $server->run();
    }

}
