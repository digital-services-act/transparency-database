<?php

namespace App\Console\Commands;

use App\Servers\StatementWebSocketServer;
use Illuminate\Console\Command;
use Ratchet\App;
use Ratchet\Server\EchoServer;

class StartStatementWebSocketServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sswss:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the WebSocketServer';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $app = new App('127.0.0.1', 6001);
        $app->route('/statement', new StatementWebSocketServer, ['*']);
        $app->run();
    }
}
