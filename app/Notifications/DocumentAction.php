<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class DocumentAction extends Notification
{
    use Queueable;

    private $action = ""; // seen, received, sent
    private $route  = null;
    private $office = null;
    private $date   = null;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($action, $route, $office)
    {
        $this->action = $action;
        $this->route = $route;
        $this->office = $office;
        $this->date = now();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    public function toHtml($notifiable) {
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $route = optional($this->route);
        $doc = optional($route->document);
        $office = optional($this->office);
        $officeName = $office->complete_name;

        $message = "{$officeName} has {$this->action} {$doc->title} 
                    ($doc->trackingId) on {$this->date}";
        return [
            "routeId"=>$route->id,
            "title"=>$doc->title,
            "trackingId"=>$doc->trackingId,
            "officeName"=>$officeName,
            "message"=>$message,
        ];
    }
}
