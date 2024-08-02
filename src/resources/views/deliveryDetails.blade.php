@extends('web::layouts.grids.8-4')

@section('title', trans('allianceindustry::ai-deliveries.delivery_title', ['code' => $delivery->delivery_code]))
@section('page_header', trans('allianceindustry::ai-deliveries.delivery_title', ['code' => $delivery->delivery_code]))

@section('left')
    <div class="row d-flex justify-content-center align-items-center h-100">
        <div class="col-lg-12 col-xl-10">
            <div class="card border-top border-bottom border-3" style="border-color: #f37a27 !important;">
                <h5 class="lead fw-bold mb-1 card-header d-flex flex-row align-items-center px-1"
                    style="color: #f37a27;">
                    {{trans('allianceindustry::ai-deliveries.delivery_title', ['code' => $delivery->delivery_code])}}
                </h5>
                <div class="card-body p-2">
                    <div class="row">
                        <div class="col mb-3">
                            <p class="small text-muted mb-1">{{trans('allianceindustry::ai-deliveries.fields.accepted')}}</p>
                            <p> @include("allianceindustry::partials.time",["date"=>$delivery->accepted])</p>
                        </div>
                        <div class="col mb-3">
                            <p class="small text-muted mb-1">{{trans('allianceindustry::ai-deliveries.fields.order')}}</p>
                            <p>
                                <a href="{{ route("allianceindustry.orderDetails",$delivery->order_id) }}">{{ $delivery->order->order_id }}</a>
                            </p>
                        </div>
                        <div class="col mb-3">
                            <p class="small text-muted mb-1">{{trans('allianceindustry::ai-deliveries.fields.producer')}}</p>
                            <p>
                                @include("web::partials.character",["character"=>$delivery->user->main_character ?? null])
                            </p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <p class="small text-muted mb-1">{{trans('allianceindustry::ai-deliveries.fields.quantity')}}</p>
                            <p>
                                {{$delivery->quantity}}
                            </p>
                        </div>
                        <div class="col mb-3">
                            <p class="small text-muted mb-1">{{trans('allianceindustry::ai-deliveries.fields.completed')}}</p>
                            <p>
                                @include("allianceindustry::partials.boolean",["value"=>$delivery->completed])
                                @if($delivery->completed_at)
                                    @include("allianceindustry::partials.time",["date"=>$delivery->completed_at])
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="d-flex flex-row">
                        @can("allianceindustry.same-user",$delivery->user_id)
                            <form action="{{ route("allianceindustry.setDeliveryState", ['deliveryId' => $delivery->id]) }}"
                                  method="POST"
                                  class="mx-1"
                            >
                                @csrf
                                @if($delivery->completed)
                                    <button type="submit"
                                            class="btn btn-warning text-nowrap confirmform btn-block"
                                            data-seat-action="{{trans('allianceindustry::ai-deliveries.mark_in_progress_action')}}">
                                        <i class="fas fa-clock"></i>&nbsp;
                                        {{trans('allianceindustry::ai-deliveries.status_in_progress')}}
                                    </button>
                                    <input type="hidden" name="completed" value="0">
                                @else
                                    <button type="submit"
                                            class="btn btn-success text-nowrap confirmform btn-block"
                                            data-seat-action="{{trans('allianceindustry::ai-deliveries.mark_delivered_action')}}">
                                        <i class="fas fa-check"></i>&nbsp;
                                        {{trans('allianceindustry::ai-deliveries.status_delivered')}}
                                    </button>
                                    <input type="hidden" name="completed" value="1">
                                @endif
                            </form>
                            @if(!$delivery->completed || auth()->user()->can("allianceindustry.admin"))
                                <form action="{{ route("allianceindustry.deleteDelivery", ['deliveryId' => $delivery->id]) }}"
                                      method="POST"
                                      class="mx-1">
                                    @csrf
                                    <button type="submit"
                                            class="btn btn-danger">
                                        <i class="fas fa-times"></i>&nbsp;
                                        {{trans('allianceindustry::ai-deliveries.cancel_delivery_btn')}}
                                    </button>
                                </form>
                            @endif
                        @endcan
                    </div>
                    <div class="mx-n5 px-5 py-4">
                        @include('allianceindustry::partials.deliveryItemTable', ['items' => $delivery->deliveryItems])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('right')
    <div class="alert alert-info" role="alert">
        <h3><i class="fas fa-info"></i>&nbsp;{{trans('allianceindustry::ai-deliveries.instructions.title')}}</h3>
        <hr/>
        <p>
            {{trans('allianceindustry::ai-deliveries.instructions.text')}}
        </p>
    </div>
    <div class="card">
        <div class="card-body">
            <label for="items">{{ trans('allianceindustry::ai-deliveries.contract.label') }}</label>
            <p>{{ trans('allianceindustry::ai-deliveries.contract.introduction') }}</p>
            @include('allianceindustry::partials.contract-summary')
            <div>
                <span><b>{{ trans('allianceindustry::ai-deliveries.contract.tip_title') }}</b></span>
                <p>{{ trans('allianceindustry::ai-deliveries.contract.tip') }}</p>
            </div>
        </div>
    </div>
@endsection
@push("javascript")
    <script>
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip()
            $('.data-table').DataTable({
                stateSave: true
            });
            $('.delivery-item-table').DataTable({
                stateSave: true,
                pageLength: 50
            });
        });
    </script>
@endpush