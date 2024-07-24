@extends('web::layouts.grids.12')

@section('title', trans('allianceindustry::ai-orders.order_marketplace_title'))
@section('page_header', trans('allianceindustry::ai-orders.order_marketplace_title'))


@section('full')
    @include('allianceindustry::partials.statistics', ['statistics' => $statistics])
    <div class="card">
        <div class="card-body">
            <h5 class="card-header d-flex flex-row align-items-baseline">
                {{trans('allianceindustry::ai-orders.open_orders_title')}}
                @can("allianceindustry.create_orders")
                    <a href="{{ route("allianceindustry.createOrder") }}"
                       class="btn btn-primary ml-auto">{{trans('allianceindustry::ai-orders.create_order_title')}}</a>
                @endcan
            </h5>
            <div class="card-text pt-3">
                @include("allianceindustry::partials.orderTable",["orders"=>$orders])
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-header d-flex flex-row align-items-baseline">
                {{trans('allianceindustry::ai-orders.your_orders_title')}}

                @if($personalOrders->where("completed",true)->isNotEmpty())
                    <form action="{{ route("allianceindustry.deleteCompletedOrders") }}" method="POST" class="ml-auto">
                        @csrf
                        <button class="btn btn-danger">{{trans('allianceindustry::ai-orders.close_all_completed_orders_btn')}}</button>
                    </form>
                @endif
            </h5>
            <div class="card-text pt-3">
                @include("allianceindustry::partials.orderTable",["orders"=>$personalOrders])
            </div>
        </div>
    </div>
@stop

@push("javascript")
    <script>
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip()
            $('.data-table').DataTable({
                stateSave: true,
            });
        });
    </script>
@endpush