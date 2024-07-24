<div class="row">
    @include('allianceindustry::partials.stats.stats-box', [
    'icon' => 'fas fa-shopping-cart',
    'value' => $statistics->completed,
    'label' => trans('allianceindustry::ai-common.statistics.completed_orders'),
    'color' => 'info'
    ])
    @include('allianceindustry::partials.stats.stats-box', [
        'icon' => 'fas fa-clock',
        'value' => $statistics->meanCompletionTime ? \Carbon\CarbonInterval::seconds((int) $statistics->meanCompletionTime)->cascade()->forHumans() : 'NA',
        'label' => trans('allianceindustry::ai-common.statistics.mean_order_completion_time'),
        'color' => 'warning'
        ])
    @include('allianceindustry::partials.stats.stats-box', [
        'icon' => 'fas fa-truck',
        'value' => $statistics->completedDeliveries,
        'label' => trans('allianceindustry::ai-common.statistics.completed_deliveries'),
        'color' => 'info'
    ])
    @include('allianceindustry::partials.stats.stats-box', [
        'icon' => 'fas fa-truck-loading',
        'value' => $statistics->meanDeliveryCompletionTime ? \Carbon\CarbonInterval::seconds((int) $statistics->meanDeliveryCompletionTime)->cascade()->forHumans() : 'NA',
        'label' => trans('allianceindustry::ai-common.statistics.mean_delivery_completion_time'),
        'color' => 'success'
        ])
</div>
