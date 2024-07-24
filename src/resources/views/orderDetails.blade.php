@extends('web::layouts.grids.8-4')

@section('title', trans('allianceindustry::ai-orders.order_title', ['code' => $order->order_id]))
@section('page_header', trans('allianceindustry::ai-orders.order_title', ['code' => $order->order_id]))

@section('left')
    <div class="row d-flex justify-content-center align-items-center h-100">
        <div class="col-lg-12 col-xl-10">
            <div class="card border-top border-bottom border-3" style="border-color: #f37a27 !important;">
                <h5 class="lead fw-bold mb-1 card-header d-flex flex-row align-items-center px-1"
                    style="color: #f37a27;">
                    {{trans('allianceindustry::ai-orders.order_title', ['code' => $order->order_id])}}
                    - {{$order->reference}}
                </h5>
                <div class="card-body p-2">
                    <div class="row">
                        <div class="col mb-3">
                            <p class="small text-muted mb-1">{{trans('allianceindustry::ai-orders.fields.date')}}</p>
                            <p> @include("allianceindustry::partials.time",["date"=>$order->created_at])</p>
                        </div>
                        <div class="col mb-3">
                            <p class="small text-muted mb-1">{{trans('allianceindustry::ai-orders.fields.code')}}</p>
                            <p>{{$order->order_id}}</p>
                        </div>
                        <div class="col mb-3">
                            <p class="small text-muted mb-1">{{trans('allianceindustry::ai-orders.fields.location')}}</p>
                            <p>@include("allianceindustry::partials.longTextTooltip",["text"=>$order->location()->name,"length"=>40])</p>
                        </div>
                        <div class="col mb-3">
                            <p class="small text-muted mb-1">{{trans('allianceindustry::ai-common.character_header')}}</p>
                            <p>@include("web::partials.character",["character"=>$order->user->main_character ?? null])</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <p class="small text-muted mb-1">{{trans('allianceindustry::ai-common.until_header')}}</p>
                            <p>@include("allianceindustry::partials.time",["date"=>$order->produce_until])</p>
                        </div>
                        <div class="col mb-3">
                            <p class="small text-muted mb-1">{{trans('allianceindustry::ai-common.completion_header')}}</p>
                            <p>
                                @include("allianceindustry::partials.boolean",["value"=>$order->completed])
                                @if($order->completed_at)
                                    @include("allianceindustry::partials.time",["date"=>$order->completed_at])
                                @endif
                            </p>
                        </div>
                        <div class="col mb-3">
                            <p class="small text-muted mb-1">{{trans('allianceindustry::ai-orders.fields.quantities')}}</p>
                            <p>
                                {{$order->assignedQuantity()}} / {{$order->totalQuantity()}}
                            </p>
                        </div>
                        @if($order->corporation != null)
                            <div class="col mb-3">
                                <p class="small text-muted mb-1">{{trans('allianceindustry::ai-orders.fields.corporation')}}</p>
                                <p>
                                    @include("web::partials.corporation",["corporation"=>$order->corporation])
                                </p>
                            </div>
                        @endif
                        @if($order->is_repeating)
                            <div class="col mb-3">
                                <p class="small text-muted mb-1">{{trans('allianceindustry::ai-orders.repeating_order_title')}}</p>
                                <p>
                                    {{trans('allianceindustry::ai-orders.repeating_order_desc', ['days' => number($order->repeat_interval,0), 'date' => $order->repeat_date])}}
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="d-flex flex-row">
                        @can("allianceindustry.same-user",$order->user_id)
                            @if($order->confirmed)
                                @if($order->deliveries->isEmpty() || !$order->hasPendingDeliveries() || $order->completed || auth()->user()->can("allianceindustry.admin"))
                                    <form action="{{ route("allianceindustry.deleteOrder") }}" method="POST"
                                          class="mx-1">
                                        @csrf
                                        <input type="hidden" name="order" value="{{ $order->id }}">
                                        <button type="submit"
                                                class="btn btn-danger">
                                            <i class="fas fa-times "></i>&nbsp;
                                            {{trans('allianceindustry::ai-orders.close_order_btn')}}
                                        </button>
                                    </form>
                                @endif

                                @if(!$order->completed && !$order->is_repeating)
                                    <form action="{{ route("allianceindustry.updateOrderPrice") }}" method="POST"
                                          class="mx-1">
                                        @csrf
                                        <input type="hidden" name="order" value="{{ $order->id }}">
                                        <button type="submit" class="btn btn-secondary confirmform"
                                                data-seat-action="{{trans('allianceindustry::ai-orders.update_price_action')}}">
                                            <i class="fas fa-dollar-sign"></i>&nbsp;
                                            {{trans('allianceindustry::ai-orders.update_price_btn')}}
                                        </button>
                                    </form>
                                @endif

                                @if(!$order->is_repeating && !$order->completed)
                                    <form action="{{ route("allianceindustry.extendOrderPrice") }}" method="POST"
                                          class="mx-1">
                                        @csrf
                                        <input type="hidden" name="order" value="{{ $order->id }}">
                                        <button type="submit" class="btn btn-secondary confirmform"
                                                data-seat-action="{{trans('allianceindustry::ai-orders.extend_time_action')}}">
                                            <i class="fas fa-clock "></i>&nbsp;
                                            {{trans('allianceindustry::ai-orders.extend_time_btn')}}
                                        </button>
                                    </form>
                                @endif
                            @else
                                <form action="{{ route("allianceindustry.confirmOrder", ['id' => $order->id]) }}"
                                      method="POST"
                                      class="mx-1">
                                    @csrf
                                    <button type="submit"
                                            class="btn btn-success">
                                        <i class="fa fa-check "></i>&nbsp;
                                        {{trans('allianceindustry::ai-orders.confirm_order_btn')}}
                                    </button>
                                </form>
                                @if($order->deliveries->isEmpty() || !$order->hasPendingDeliveries() || $order->completed || auth()->user()->can("allianceindustry.admin"))
                                    <form action="{{ route("allianceindustry.deleteOrder") }}" method="POST"
                                          class="mx-1">
                                        @csrf
                                        <input type="hidden" name="order" value="{{ $order->id }}">
                                        <button type="submit"
                                                class="btn btn-danger">
                                            <i class="fas fa-times "></i>&nbsp;
                                            {{trans('allianceindustry::ai-orders.close_order_btn')}}
                                        </button>
                                    </form>
                                @endif
                            @endif
                        @endcan
                        @if($order->confirmed)
                            @can('allianceindustry.corp_delivery')
                                <form action="{{ route("allianceindustry.toggleReserveCorp", ['id' => $order->id]) }}"
                                      method="POST"
                                      class="mx-1">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary">
                                        @if($order->corporation != null)
                                            <i class="fas fa-times "></i>&nbsp;
                                        @else
                                            <i class="fas fa-check "></i>&nbsp;
                                        @endif
                                        {{trans('allianceindustry::ai-orders.reserve_corp_btn')}}
                                    </button>
                                </form>
                                @if($order->assignedQuantity() < $order->totalQuantity() && $order->corp_id == auth()->user()->main_character->affiliation->corporation_id)
                                    <a
                                            href="{{ route("allianceindustry.prepareDelivery", ['id' => $order->id]) }}"
                                            class="btn btn-primary mx-1 ml-auto">
                                        <i class="fas fa-truck"></i>&nbsp;
                                        {{trans('allianceindustry::ai-deliveries.order_create_delivery_btn')}}
                                    </a>
                                @endif
                            @endcan
                            @if($order->assignedQuantity() < $order->totalQuantity() && !$order->corporation)
                                <a href="{{ route("allianceindustry.prepareDelivery", ['id' => $order->id]) }}"
                                   class="btn btn-primary mx-1 ml-auto">
                                    <i class="fas fa-truck"></i>&nbsp;
                                    {{trans('allianceindustry::ai-deliveries.order_create_delivery_btn')}}
                                </a>
                            @endif
                        @endif
                    </div>

                    @if($order->items->count() > 1)
                        <div class="mx-n5 px-5 py-4">
                            @include('allianceindustry::partials.orderItemTable', ['items' => $order->items])
                        </div>
                    @endif

                    @if(!$order->is_repeating)
                        @if(!$order->deliveries->isEmpty())
                            <p class="lead fw-bold mb-4 pb-2"
                               style="color: #f37a27;">{{trans('allianceindustry::ai-deliveries.deliveries_title')}}</p>
                            @include("allianceindustry::partials.deliveryTable",["deliveries"=>$order->deliveries])
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
@section('right')
    <div class="card">
        <div class="card-body">
            <label for="items"> {{ trans('allianceindustry::ai-orders.summary.title') }}</label>
            @include('allianceindustry::partials.order-summary', ['order' => $order])
        </div>
    </div>
@stop

@push("javascript")
    <script>
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip()
            $('.data-table').DataTable({
                stateSave: true
            });
            $('.order-item-table').DataTable({
                stateSave: true,
                pageLength: 50
            });
        });
    </script>
@endpush