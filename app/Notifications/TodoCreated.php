<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class TodoCreated extends Notification
{
    use Queueable;

    protected $todo;

    public function __construct($todo)
    {
        $this->todo = $todo;
    }

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('Neues Todo erstellt')
            ->icon('/icon.png') // Pfad zum Icon
            ->body("{$this->todo->content}")
            ->action('Ansehen', 'view_app');
    }
}
