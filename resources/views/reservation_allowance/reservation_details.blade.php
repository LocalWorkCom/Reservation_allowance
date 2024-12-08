<style>
    .div-info {
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
        width: 200px;
        height: 200px;
        background-color: #F6F7FD;
        border: 1px solid #D9D9D9 !important;
    }

    .div-info-padding {
        padding: 3px 0;
        direction: initial;
        font-family: Almarai;
        font-size: 24px;
        font-weight: 700;
        line-height: 36px;
        text-align: right;

    }

    .div-info-padding b span {
        color: #032F70;
    }

    .custom-select {
        width: 100%;
        color: green !important;
        border-radius: 10px !important;
        height: 43px !important;
        background-color: #fafbfd !important;
    }

    .custom-select-lg {
        /* height: calc(2.45rem + 0px) !important; */
        padding-top: 0.375rem;
        padding-bottom: .375rem;
        font-size: 125%;
        margin-inline: 5px !important;
    }
</style>


@extends('layout.main')
@push('style')
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
    <script type="text/javascript" charset="utf8"
        src="https://code.jquery.com/jquery-3.5.1.js" defer></script>
    <script type="text/javascript" charset="utf8"
        src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer>
    </script>
@endpush

@section('title')
    القطاعات
@endsection
@section('content')
    <div class="row">
        <div class="container welcome col-11">
            <div class="d-flex justify-content-between">

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif


                <p> عرض بدل حجز - {{$type == 1 ? 'كلى' : 'جزئى'}} @if($employee->grade) - {{$employee->grade->name}} @endif - {{$employee->name}} @if($sectorDetails) - {{$sectorDetails->name}}@endif @if($departementDetails) - {{$departementDetails->name}} @endif</p>
            </div>


            <!--  <div class="d-flex justify-content-between mt-2">
                                    <div class=" mx-2">
                                        {{-- @if (Auth::user()->hasPermission('create reservation_allowances')) --}}
                                        <a class="btn-all py-2 px-2 " href="{{ route('reservation_allowances.create') }}"
                                            style="color: #0D992C;">
                                            <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">
                                            اضافة بدل حجز جديد
                                        </a>
                                        {{-- @endif --}}
                                    </div> -->

        </div>
        <!-- show_reservation_allowances_info -->
        <div id="show_reservation_allowances_info" class="col-12"></div>
        <!-- end of show_reservation_allowances_info -->
    </div>
    </div>
    </div>
    </div>
    </div>

    <br>
    <div class="row">
        <div class="container col-11 p-4">
            <div class="d-flex flex-wrap" dir="rtl" style="background-color:transparent;">
                <h4 id="current_sector"></h4>
                <h4 id="current_department"></h4>
            </div>


            <div class="col-lg-12">
                <div class="bg-white">
                    @if (session()->has('message'))
                        <div class="alert alert-info">
                            {{ session('message') }}
                        </div>
                    @endif

                    <div>
                        <table id="users-table"
                            class="display table table-responsive-sm  table-bordered table-hover dataTable" dir="rtl">
                            <thead>
                                <!-- First Row: Group Headers -->
                                <tr>
                                    <th style="width:5%">م</th>
                                    <th>توقيت اضافة بدل الحجز</th>
                                    <th>المبلغ</th>
                                    <th>القائم بالحجز</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($get_allowance_results)
                                    @foreach($get_allowance_results as $k_get_allowance_results=>$get_allowance_results)
                                        <tr>
                                            <td>{{$k_get_allowance_results+1}}</td>
                                            <td>{{$get_allowance_results->created_at}}</td>
                                            <td>{{$get_allowance_results->amount}} د.ك</td>
                                            <td>{{$get_allowance_results->creator->name}}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>

                        <p style=" font-weight: bold; font-size: 20px;color: #274373;margin-top: 10px;" class="mx-2">
                            المجموع الكلي: <span id="total-amount">{{$total}}</span>
                        </p>

                    </div>
                </div>
            </div>


        </div>
    </div>


@endsection
@push('scripts')

@endpush
