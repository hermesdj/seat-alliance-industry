@extends('web::layouts.grids.12')

@section('title', trans('allianceindustry::ai-deliveries.your_deliveries_title'))
@section('page_header', trans('allianceindustry::ai-deliveries.your_deliveries_title'))


@section('full')
    <div class="card">
        <div class="card-body">
            <h5 class="card-header d-flex flex-row align-items-baseline">
                {{trans('allianceindustry::ai-deliveries.your_deliveries_title')}}
            </h5>
            <div class="card-text pt-3">
                @include("allianceindustry::partials.deliveryTable",["deliveries"=>$deliveries,"showOrder"=>true])
            </div>
        </div>
    </div>
@stop

@push("javascript")
    <script>
        $(document).ready( function () {
            $('[data-toggle="tooltip"]').tooltip()
            $('.data-table').DataTable({
                stateSave: true
            });
        });
    </script>
@endpush