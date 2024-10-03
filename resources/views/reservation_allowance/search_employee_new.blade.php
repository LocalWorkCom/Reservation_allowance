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
</style>


@extends('layout.main')
@push('style')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
<script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js" defer></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer>
</script>
@endpush

@section('title')
القطاعات
@endsection
@section('content')
<div class="row">

    <div class="container welcome col-11" style="height: auto !important">
        <div class="d-flex justify-content-between">
            <div class="col-12">
                <div class="row" style="direction: rtl">
                        <div class="col-2">
                            <p> بدل الحجز</p>
                        </div>

                        <form class="col-6" action="{{ route('reservation_allowances.search_employee_new') }}" method="post" enctype="multipart/form-data">
                        @csrf
                            <div class="row">
                                <!-- 1 for sector , 2 for department -->
                                <input name="department_type" id="department_type" type="hidden" value="{{Auth::user()->department_id == null ? 1 : 2}}">

                                <div class="col-4">{{-- @if (Auth::user()->hasPermission('create reservation_allowances')) --}}
                                    <label for="Civil_number"> <i class="fa-solid fa-asterisk" style="color:red; font-size:10px;"></i>اختار القطاع</label>
                                    <select class="custom-select custom-select-lg mb-3 select2" name="sector_id" id="sector_id">
                                        <option value="0" selected disabled>اختار من القائمة</option>
                                            @foreach ($sectors as $sector)
                                            <option value="{{ $sector->id }}" {{$sector_id == $sector->id ? "selected" : ""}}>
                                                {{ $sector->name }}</option>
                                            @endforeach
                                    </select>
                                </div>

                                <div class="col-4">{{-- @if (Auth::user()->hasPermission('create reservation_allowances')) --}}
                                    <label for="Civil_number"> <i class="fa-solid fa-asterisk" style="color:red; font-size:10px;"></i>اختار الادارة</label>
                                    <select class="custom-select custom-select-lg mb-3 select2" name="departement_id" id="departement_id">
                                        <option value="0" selected disabled>اختار من القائمة</option>
                                        @if($get_departements)
                                            @foreach($get_departements as $departement)
                                                <option value="{{ $departement->id }}" {{$departement_id == $departement->id ? "selected" : ""}}>{{ $departement->name }}</option>
                                                @if(count($departement->children))
                                                    @include('reservation_allowance.manageChildren',['children' => $departement->children, 'parent_id'=>''])
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <div class="col-4">
                                    <label for="Civil_number">
                                    <button class="btn-all py-2 px-2" type="submit">
                                        <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">
                                        بحث عن موظفين
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="col-2">{{-- @if (Auth::user()->hasPermission('create reservation_allowances')) --}}
                            <a class="btn-all py-2 px-2" href="{{ route('reservation_allowances.create') }}"
                                style="color: #0D992C;">
                                <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">

                                اضافة بدل حجز جديد
                            </a>
                            {{-- @endif --}}
                        </div>
                        <div class="col-2">{{-- @if (Auth::user()->hasPermission('create reservation_allowances')) --}}
                            <a class="btn-all py-2 px-2" href="{{ route('reservation_allowances.create.all') }}"
                                style="color: #0D992C;">
                                <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">

                                اضافة بدل حجز كلى جديد
                            </a>
                            {{-- @endif --}}
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
    <div class="container  col-11 mt-3 p-0  pt-5 pb-4">

        <div class="col-lg-12">
            <div class="bg-white">
                @if (session()->has('message'))
                <div class="alert alert-info">
                    {{ session('message') }}
                </div>
                @endif

                <div>
                    <table id="users-table" class="display table table-responsive-sm  table-bordered table-hover dataTable" style="direction:rtl">
                        <thead>
                            <tr>
                                <th>الرتيب</th>
                                <th>الرتبة</th>
                                <th>الاسم</th>
                                <th>رقم الملف</th>
                                <th>بدل الحجز</th>
                                <!-- <th style="width:150px;">العمليات</th>-->
                            </tr>
                        </thead>
                        @if($employees)
                            @foreach($employees as $employee)
                                <tr>
                                    <th>{{$employee->id}}</th>
                                    <th>{{$employee->name}}</th>
                                    <th>{{$employee->grade_id != null ? $employee->grade->name : "لا يوجد رتبة"}}</th>
                                    <th>{{$employee->file_number != null ? $employee->file_number : "لا يوجد رقم ملف"}}</th>
                                    <th>
                                        <div class="d-flex" style="justify-content: space-around !important">
                                            <div style="display: inline-flex; direction: ltr;">
                                                <label for="">  حجز كلى</label>
                                                <input type="radio" name="allowance[][{{$employee->id}}]" id="allowance_{{$employee->id}}" onclick="add_to_cache(1, {{$employee->id}})" value="1" class="form-control">
                                            </div>
                                            <span>|</span>
                                            <div style="display: inline-flex; direction: ltr;">
                                                <label for="">  حجز جزئى</label>
                                                <input type="radio" name="allowance[][{{$employee->id}}]" id="allowance_{{$employee->id}}" onclick="add_to_cache(2, {{$employee->id}})" value="2" class="form-control">
                                            </div>
                                            <span>|</span>
                                            <div style="display: inline-flex; direction: ltr;">
                                                <label for="">  لا يوجد</label>
                                                <input type="radio" name="allowance[][{{$employee->id}}]" id="allowance_{{$employee->id}}" onclick="add_to_cache(0, {{$employee->id}})" value="0" checked class="form-control">
                                            </div>
                                        </div>
                                    </th>
                                    <!-- <th style="width:150px;">العمليات</th>-->
                                </tr>
                            @endforeach
                        @endif
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>





@endsection
@push('scripts')

<script>
$(document).ready(function() {
    function closeModal() {
        $('#delete').modal('hide');
    }

    $('#closeButton').on('click', function() {
        closeModal();
    });

    
    $(document).on("change", "#sector_id", function () {
        var sectorid = this.value;
        var department_type = document.getElementById('department_type').value;
        var map_url = "{{route('reservation_allowances.get_departement',['id','type'])}}";
        map_url = map_url.replace('id', sectorid);
        map_url = map_url.replace('type', department_type);
        $.get(map_url, function(data){
            $("#departement_id").html(data);
        });
    });


    $(document).on("click", "#sector_id00", function () {
        var sectorid = this.value;
        var department_type = document.getElementById('department_type').value;
        var map_url = "{{route('reservation_allowances.get_departement',['id','type'])}}";
        map_url = map_url.replace('id', sectorid);
        map_url = map_url.replace('type', department_type);
        $.get(map_url, function(data){
            $("#departement_id").html(data);
        });
    });


});

function add_to_cache($type, $id)
{
    var department_type = document.getElementById('department_type').value;
    var map_url = "{{route('reservation_allowances.add_reservation_allowances_employess',['type','id'])}}";
    map_url = map_url.replace('id', $id);
    map_url = map_url.replace('type', $type);
    $.get(map_url, function(data){
        //$("#departement_id").html(data);
    });
}



$(function() {
    $(".select2").select2({
        dir: "rtl"
    });
});
</script>


@endpush