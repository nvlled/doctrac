<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateRootCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maint:create-root';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a root account for administration';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \App\Maint::createRootAccount();
    }
}
