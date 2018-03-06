<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature =
        'send:email
            {office : username or ID}
            {message : email message}
            {--subject= : email subject}
            {--from-name= : sender name}
            {--from-address= : sender address}
        ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send an email to an office';

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
        $officeId = $this->argument("office");
        $user = \App\User::where("username", $officeId)->first();
        $office = null;
        if ($user)
            $office = $user->office;
        else
            $office = \App\User::find($officeId);

        if (!$office) {
            $this->error("invalid office ID or username: $officeId");
            return;
        }

        $this->info("sending email to $officeId...");
        $emails = $office->emails;

        if ($emails->count() == 0) {
            $this->error("office has no specified email/s");
            return;
        }

        $primaryEmail = $emails->first()->data;
        $otherEmails  = $emails->slice(1)->map(function($email) {
            return $email->data;
        });

        $contents = $this->argument("message");
        $fromAddress = $this->option("from-address");
        $fromName = $this->option("from-name");
        $subject = $office->complete_name;

        if ($this->option("subject"))
            $subject .= ": " .$this->option("subject");

        \Mail::to($primaryEmail)
            ->bcc($otherEmails)
            ->send(new \App\Mail\OfficeEmailMessage($office, $contents, $subject));
    }
}
