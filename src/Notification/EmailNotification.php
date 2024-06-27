<?php

namespace App\Notification;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailNotification implements NotificationInterface
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function notify(string $message, string $recipient): void
    {
        $email = (new Email())
            ->from('no-reply@laliga.com')
            ->to($recipient)
            ->subject('Notification')
            ->text($message);

        $this->mailer->send($email);
    }

}