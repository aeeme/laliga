<?php

namespace App\Notification;

use Grpc\Channel;

class Notifier
{
    /**
     * @var NotificationInterface[]
     */

    private array $channels = [];

    public function addChannel(NotificationInterface $channel): void
    {
        $this->channels[] = $channel;
    }

    public function notify(string $message, string $recipient): void
    {
        foreach ($this->channels as $channel) {
            $channel->notify($message, $recipient);
        }
    }
}