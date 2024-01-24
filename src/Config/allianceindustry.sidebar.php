<?php
return [
    'allianceindustry' => [
        'name' => 'Alliance Industry',
        'label' => 'allianceindustry::ai-common.menu_title',
        'icon' => 'fas fa-industry',
        'route_segment' => 'allianceindustry',
        'permission' => 'allianceindustry.view_orders',
        'entries' => [
            [
                'name' => 'Orders',
                'label' => 'allianceindustry::ai-common.menu_orders',
                'icon' => 'fas fa-list',
                'route' => 'allianceindustry.orders',
                'permission' => 'allianceindustry.view_orders',
            ],
            [
                'name' => 'Deliveries',
                'label' => 'allianceindustry::ai-common.menu_deliveries',
                'icon' => 'fas fa-truck',
                'route' => 'allianceindustry.deliveries',
                'permission' => 'allianceindustry.view_orders',
            ],
            [
                'name' => 'Settings',
                'label' => 'allianceindustry::ai-common.menu_settings',
                'icon' => 'fas fa-cogs',
                'route' => 'allianceindustry.settings',
                'permission' => 'allianceindustry.settings',
            ],
            [
                'name' => 'About',
                'label' => 'allianceindustry::ai-common.menu_about',
                'icon' => 'fas fa-info',
                'route' => 'allianceindustry.about',
                'permission' => 'allianceindustry.view_orders',
            ],
        ]
    ]
];