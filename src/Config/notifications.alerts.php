<?php

return [
    'seat_alliance_industry_new_order_notification' => [
        'label' => 'allianceindustry::ai-config.seat_alliance_industry_new_order_notification',
        'handlers' => [
            'mail' => \RecursiveTree\Seat\AllianceIndustry\Notifications\OrderNotificationMail::class,
            'slack' => \RecursiveTree\Seat\AllianceIndustry\Notifications\OrderNotificationSlack::class,
            'discord' => \RecursiveTree\Seat\AllianceIndustry\Notifications\OrderNotificationDiscord::class
        ],
    ]
];