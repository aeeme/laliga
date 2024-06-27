<?php

namespace App\Notification;

interface NotificationInterface
{
    public function notify(string $message, string $recipient): void;
}