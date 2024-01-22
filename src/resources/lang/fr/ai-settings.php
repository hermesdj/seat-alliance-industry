<?php

return [
    'settings_title' => 'Paramètres',

    'price_settings_title' => 'Paramètres des prix',

    'default_price_provider_label' => 'Fournisseur de prix par défaut',
    'default_price_provider_hint' => 'Le fournisseur de prix par défaut pour les ordres. Gérer le fournisseur de prix dans <a href=":route">se trouvant dans les paramètres des prix</a>.',

    'mmpp_label' => 'Pourcentage de profit minimum',
    'mmpp_hint' => 'Pour inciter à la production, le plugin applique ce % à l\'item à son prix le plus haut. À la création de l\'ordre, vous pouvez toujours choisir de donner un plus grand profit, mais pour éviter que les joueurs se fassent la guerre, ils ne peuvent pas descendre en-dessous de cette valeur.',
    //To incentive production, the plugin applies this % of the item value on top of the price. While creating an order, you can always choose to give a higher profit, but to avoid players ripping off others, they can\'t go below this value .

    'allow_manual_prices_label' => 'Autoriser les prix manuels à être en-dessous du prix automatique',
    'allow_manual_prices_hint' => 'Pour éviter les prix frauduleux, les prix manuels sont ignorés si ils sont en-dessous du prix automatique.',

    'allow_changing_price_provider_label' => 'Autoriser les utilisateurs à changer le fournisseur de prix lors de la création d\'un ordre.'
    'allow_changing_price_provider_hint' => 'Pour éviter les ordres frauduleux, il est recommandé de laisser cette option désactivée.',

    'general_settings_title' => 'Paramètres général',

    'remove_expired_deliveries_label' => 'Supprimer les livraisons expirés',
    'remove_expired_deliveries_hint' => 'Si une livraison n\'est pas complété avant l\'expiration de l\'ordre, la livraison est supprimée.'

    'default_location_label' => 'Localisations par défaut',
    'default_location_hint' => 'Contrôle les locations présélectionner lors de la création d\'un ordre'

    'notifications_ping_role_label' => 'Notifications: Roles à ping lors de le création d\'un ordre'
    'notifications_ping_role_hint' => 'Copier/coller les IDs des rôles Discord en laissant un espace entre chacun d\'eux. Si vous avez le mode développeur activé dans vos paramètre, il suffit de cliquer sur le rôle pour avoir son ID.',

    'update_settings_btn' => 'Mettre à jours les paramètres',
    'update_settings_success' => 'Mise à jour des paramètres effectués.'
];