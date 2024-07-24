<table class="data-table table table-striped table-hover">
    <thead>
    <tr>
        <th>{{trans('allianceindustry::ai-deliveries.headers.code')}}</th>
        @if($showOrder ?? false)
            <th>{{trans('allianceindustry::ai-orders.order_id')}}</th>
        @endif
        <th>{{trans('allianceindustry::ai-common.amount_header')}}</th>
        <th>{{trans('allianceindustry::ai-common.completion_header')}}</th>
        @if($showOrder ?? false)
            <th>{{trans('allianceindustry::ai-common.order_price_header')}}</th>
        @endif
        <th>{{trans('allianceindustry::ai-common.delivery_price_header')}}</th>
        <th>{{trans('allianceindustry::ai-common.accepted_header')}}</th>
        @if($showOrder ?? false)
            <th>{{trans('allianceindustry::ai-common.ordered_by_header')}}</th>
        @endif
        <th>{{trans('allianceindustry::ai-common.producer_header')}}</th>
        <th>{{trans('allianceindustry::ai-common.location_header')}}</th>
    </tr>
    </thead>
    <tbody>
    @foreach($deliveries as $delivery)
        <tr>
            <td>
                <a href="{{ route("allianceindustry.deliveryDetails",$delivery->id) }}">{{$delivery->delivery_code}}</a>
            </td>
            @if($showOrder ?? false)
                <td>
                    <a href="{{ route("allianceindustry.orderDetails",$delivery->order_id) }}">{{ $delivery->order->order_id }}</a>
                </td>
            @endif
            <td data-order="{{ $delivery->totalQuantity() }}" data-filter="_">
                {{ number($delivery->totalQuantity(),0) }}
            </td>
            <td data-order="{{ $delivery->completed_at?carbon($delivery->completed_at)->timestamp:0 }}" data-filter="_">
                @include("allianceindustry::partials.boolean",["value"=>$delivery->completed])
                @if($delivery->completed_at)
                    @include("allianceindustry::partials.time",["date"=>$delivery->completed_at])
                @endif
            </td>
            @if($showOrder ?? false)
                <td data-order="{{ $delivery->order->price }}" data-filter="_">
                    {{ number($delivery->order->price / 100) }} ISK
                </td>
            @endif
            <td data-order="{{ $delivery->order->price * $delivery->quantity }}" data-filter="_">
                {{ number($delivery->totalPrice() / 100) }} ISK
            </td>
            <td data-order="{{ $delivery->accepted }}" data-filter="_">
                @include("allianceindustry::partials.time",["date"=>$delivery->accepted])
            </td>
            @if($showOrder ?? false)
                <td data-order="{{ $delivery->order->user->id ?? 0}}"
                    data-filter="{{ $delivery->order->user->main_character->name ?? trans('web::seat.unknown')}}">
                    @include("web::partials.character",["character"=>$delivery->order->user->main_character ?? null])
                </td>
            @endif
            <td data-order="{{ $delivery->user->id ?? 0}}"
                data-filter="{{ $delivery->user->main_character->name ?? trans('web::seat.unknown')}}">
                @include("web::partials.character",["character"=>$delivery->user->main_character ?? null])
            </td>
            <td data-order="{{ $delivery->order->location_id }}" data-filter="{{ $delivery->order->location()->name }}">
                @include("allianceindustry::partials.longTextTooltip",["text"=>$delivery->order->location()->name])
            </td>
        </tr>
    @endforeach
    </tbody>
</table>