<?php

return [
    'menu_title' => 'Planificateur de l'industrie de l'Alliance',
    'menu_orders' => 'Commandes',
    'menu_deliveries' => 'Livraisons',
    'menu_settings' => 'Paramètres',
    'menu_about' => 'À propos',

    'cancel' => 'Annuler',
    'back' => 'Retour',

    'price_provider_label' => 'Fournisseur de prix',
    'price_provider_hint' => 'La source des prix utilisée pour calculer le prix de la commande.',
    'price_provider_create_success' => 'Fournisseur de prix créé avec succès.',

    'amount_header' => 'Montant',
    'completion_header' => 'Complété',
    'price_header' => 'Prix',
    'unit_price_header' => 'Prix unitaire',
    'total_price_header' => 'Prix total',
    'accepted_header' => 'Accepté',
    'ordered_by_header' => 'Commandé par',
    'producer_header' => 'Producteur',
    'location_header' => 'Emplacement',

    'tags_header' => 'Tags',
    'quantity_header' => 'Quantité',
    'created_header' => 'Créé',
    'until_header' => 'Jusqu'à',
    'character_header' => 'Personnage',

    'actions_header' => 'Actions',

    'other_label' => ', +:count autre',

    'repeating_badge' => 'Répéter',

    'edit_price_provider_title' => 'Modifier le fournisseur de prix',
    'manufacturing_time_modifier_label' => 'Modificateur de temps de fabrication',
    'reaction_time_modifier_label' => 'RModificateur de temps de réaction',

    'notifications_field_description' => 'Priorité: :priorité | :prix ISK/unité | :Prixtotal ISK Total | :emplacement',
    'notification_more_items' => 'Plus d\'Articles',

    'error_no_price_provider' => 'Aucun fournisseur de prix configuré ou sélectionné!',
    'error_minimal_profit_too_low' => 'Le profit minimal ne peut être inférieur à :mpp%',
    'error_structure_not_found' => 'Impossible de trouver la structure/station.',

    // Orders error
    'error_order_is_empty' => 'Vous devez ajouter au moins 1 article à la livraison',
    'error_order_not_found' => 'La commande n'a pas été trouvée',
    'error_obsolete_order' => 'Can\'t update pre-seat-5 orders due to breaking internal changes.',
    'error_price_provider_get_prices' => 'Le fournisseur de prix n\'a pas réussi à récupérer les prix: :message',
    'error_deleted_in_progress_order' => 'Vous ne pouvez pas supprimer les commandes que des personnes sont en train de fabriquer !',

    // Deliveries errors
    'error_delivery_not_assignable_to_repeating_order' => 'Les commandes répétées ne peuvent pas être livrées',
    'error_no_quantity_provided' => 'La quantité doit être supérieure à.0',
    'error_too_much_quantity_provided' => 'La quantité doit être inférieure à la quantité restante',
    'error_delivery_not_found' => 'Impossible de trouver la livraison',
];