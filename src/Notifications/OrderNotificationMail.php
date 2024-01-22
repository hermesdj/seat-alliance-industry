<?php

namespace RecursiveTree\Seat\AllianceIndustry\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\SerializesModels;
use Seat\Notifications\Notifications\AbstractMailNotification;

class OrderNotificationMail extends AbstractMailNotification implements ShouldQueue
{
    use SerializesModels;

    private $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function populateMessage(MailMessage $message, $notifiable)
    {

        $message->success()
            ->subject(trans('allianceindustry::ai-config.notification_mail_subject'))
            ->greeting(trans('allianceindustry::ai-config.notification_mail_greeting'))
            ->line(trans('allianceindustry::ai-config.notification_mail_line'))
            ->action(trans('allianceindustry::ai-config.notification_mail_action'), route("allianceindustry.orders"));

        $message->salutation(trans('allianceindustry::ai-config.notification_mail_salutation'));

        return $message;
    }
}