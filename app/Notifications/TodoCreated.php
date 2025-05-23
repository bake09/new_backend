<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class TodoCreated extends Notification
{
    protected $todo;
    protected $sender;

    public function __construct($todo, $sender)
    {
        $this->todo = $todo;
        $this->sender = $sender;
    }

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('Neues Todo von : ' . $this->sender)
            ->icon('/icon.png') // Pfad zum Icon
            ->body("{$this->todo->content}")
            ->action('Ansehen', 'view_app');
    }
}
