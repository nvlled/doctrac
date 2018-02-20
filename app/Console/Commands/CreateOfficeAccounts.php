<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateOfficeAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:create-office-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        foreach (\App\Office::all() as $off) {
            $user = new \App\User();
            $user->username  = strtolower("{$off->campus_code}-{$off->name}");
            $user->firstname = "";
            $user->middlename = "";
            $user->lastname = "";
            $user->officeId = $off->id;
            $user->positionId = 0;
            $user->privilegeId = 0;
            $user->password = bcrypt("x");
            $user->save();
        }
    }
}
