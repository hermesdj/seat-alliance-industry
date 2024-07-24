<?php

return [
    'create_order_title' => 'Créer une Commande',

    'order_title' => 'Commande N°:code',
    'orders_title' => 'Commandes',
    'order_reference' => 'Référence Cde',
    'your_orders_title' => 'Vos Commandes',

    'repeating_order_title' => 'Commande répétée',
    'repeating_order_desc' => 'Ceci est une commande qui se republie elle-même chaque :days jours. La prochaine republication au lieu le :date.',

    'items_label' => 'Items',
    'items_placeholder' => "MULTIBUY:\nTristan 100\nOmen 100\nTritanium 30000\n\nFITTINGS:\n[Pacifier, 2022 Scanner]\n\nCo-Processor II\nCo-Processor II\n\nMultispectrum Shield Hardener II\nMultispectrum Shield Hardener II\n\nSmall Tractor Beam II\nSmall Tractor Beam II",
    'split_items' => 'Séparer les objets en différentes commandes.',

    'reward_label' => 'Récompense en %',
    'reward_hint' => 'Le profit minimum est de :mpp',
    'add_profit_to_manual_prices_label' => 'Ajouter une marge au prix manuel',

    'days_to_complete_label' => 'Jours pour compléter',

    'location_label' => 'Localisation',
    'priority_label' => 'Priorité',
    'priority_Very Low' => 'Très basse',
    'priority_Low' => 'basse',
    'priority_Normal' => 'Normal',
    'priority_Preferred' => 'Priorisé',
    'priority_Important' => 'Important',
    'priority_Critical' => 'Critique',

    'quantity_label' => 'Quantité',
    'quantity_hint' => 'Le nombre de commandes à passer pour ce qui a été collé dans le champ. Utile pour commander plusieurs fois le même fit de vaisseau par exemple.',

    'reference_label' => 'Référence',
    'reference_hint' => 'Vous pouvez donner un nom à votre commande. Laisser ce champ vide le remplira automatiquement avec un ID généré. Si vous collez un fit dans le champ de saisie principal, et laissez ce champ vide, le nom du fit sera utilisé comme référence.',

    'seat_inventory_label' => 'Seat-Inventory',
    'seat_inventory_hint' => 'Ajouter une source à seat-inventory',
    'seat_inventory_desc' => 'Dès que la livraison pour cette commande sera créée, une source d\'objet sera ajoutée à seat-inventory. Une fois que la livraison est marquée comme terminée, la source sera retirée. La source sera ajoutée au <u>premier</u> espace de travail contenant le label <code>add2allianceindustry</code> à n\'importe quelle position de son nom. Vous pouvez renommer les espaces de travail <a href=":route">içi</a>.',
    //'seat_inventory_desc' => 'As soon as a delivery for this order is created, a item source will be added to seat-inventory. Once the delivery is marked as completed, the source will be removed. The source will be added to the <u>first</u> workspace containing <code>add2allianceindustry</code> at any position in it\'s name. You can rename workspaces <a href=":route">here.</a>',

    'repetition_label' => 'Répétition',
    'repetition_never' => 'Jamais',
    'repetition_weekly' => 'Hebdomadaire',
    'repetition_every_two_weeks' => 'Bi-hebdomadaire',
    'repetition_monthly' => 'Mensuel',

    'no_delivery' => 'Pas de Livraison',

    'add_order_btn' => 'Ajouter une commande',

    'invalid_order_label' => 'Commande invalide',

    'close_order_btn' => 'Fermer Commande',
    'update_price_btn' => 'Mettre à jour le prix',
    'update_price_action' => 'Mettre à jour le prix ? Le prix manuel sera écrasé !',
    'extend_time_btn' => 'Ajouter du temps',
    'extend_time_action' => ' souhaite ajouter 1 semaine au temps de livraison',

    // Marketplace
    'order_marketplace_title' => 'Marché des Commandes',
    'open_orders_title' => 'Voir les commandes',

    'close_all_completed_orders_btn' => 'Fermer toutes les commandes complétées',

    'create_order_success' => 'Ajout d\'une nouvelle commande réussi !',
    'update_time_success' => 'Temps ajouté !',
    'update_price_success' => 'Prix mis à jour !',
    'close_order_success' => 'Fermeture de la commande réussi !',

    'order_id' => 'Code Commande',
    'reserve_corp_btn' => 'Reservé par la Corp.',
    'confirm_order_btn' => 'Confirmer Commande',

    'items' => [
        'headers' => [
            'type' => 'Objet',
            'quantity' => 'Quantité',
            'unit_price' => 'Px Unitaire',
            'total' => 'Total'
        ]
    ],

    'fields' => [
        'date' => 'Date',
        'code' => 'N° Commande',
        'location' => 'Lieu de Livraison',
        'quantities' => 'Quantités Totales',
        'corporation' => 'Reservé par'
    ],

    'summary' => [
        'title' => 'Résumé',
        'order_total' => 'Total Commandé',
        'in_delivery' => 'En Cours de Livraison',
        'delivered' => 'Livré',
        'remaining' => 'Reste à Livrer',
        'reference' => 'Référence',
    ],

    'notifications' => [
        'new_order' => 'Nouvelle commande :code disponible !',
        'order_details' => 'Détails de la Commande :',
        'order_price' => 'Prix',
        'nb_items' => 'Nb Lignes',
        'location' => 'Lieu',
        'reference' => 'Référence',
        'expiring_order' => 'Commande :code va expirer !',
        'expiring_message' => 'Cette commande va expirer dans :remaining !'
    ]
];