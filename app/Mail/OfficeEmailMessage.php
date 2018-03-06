<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OfficeEmailMessage extends Mailable
{
    use Queueable, SerializesModels;

    public $office = null;
    public $contents = null;
    public $subject = null;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(\App\Office $office, $contents, $subject="")
    {
        $this->office  = $office;
        $this->subject   = $subject;
        $this->contents = $contents;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.office');
    }
}
