<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\NexmoMessage;

class DocumentAction extends Notification
{
    use Queueable;

    private $action    = ""; // seen, received, sent
    private $srcOffice = null;
    private $dstOffice = null;
    private $route     = null;
    private $date      = null;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($action, $srcOffice, $dstOffice, $route)
    {
        $this->action    = $action;
        $this->srcOffice = $srcOffice;
        $this->dstOffice = $dstOffice;
        $this->route     = $route;
        $this->date      = now();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $data     = $this->toArray($notifiable);
        $office   = $this->route->office;
        $contents = $data["message"];
        $subject  = $data["trackingId"] . " update";
        $email    = $office->primary_email;
        \Log::debug("sending email to {$office->primary_email}, [$subject]");
        $mailable = new \App\Mail\OfficeEmailMessage($office, $contents, $subject);
        return $mailable->to($email);
    }

    public function toHtml($notifiable) {
    }

    public function toNexmo($notifiable)
    {
        $data = $this->toArray($notifiable);
        return (new NexmoMessage)
            ->from("NEXMO")
            ->content($data["message"]);
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $officeName = "";
        switch ($this->action) {
        case "sent":
            $officeName = $this->srcOffice->complete_name;
            break;
        case "received":
            $officeName = $this->dstOffice->complete_name;
            break;
        case "seen":
            $officeName = $this->dstOffice->complete_name;
            break;
        }

        $doc = optional($this->route)->document;
        $message = "{$officeName} has {$this->action} {$doc->title} 
                    [$doc->trackingId] on {$this->date}";
        return [
            "date"=>$this->date->toDateTimeString(),
            "action"=>$this->action,
            "routeId"=>$this->route->id,
            "title"=>$doc->title,
            "trackingId"=>$doc->trackingId,
            "officeName"=>$officeName,
            "message"=>$message,
        ];
    }
}
