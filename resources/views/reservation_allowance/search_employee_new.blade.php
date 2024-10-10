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
</style>


@extends('layout.main')
@push('style')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
<script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js" defer></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer>
</script>
@endpush

@section('title')
اضافة بدل حجز
@endsection
@section('content')
<div class="row">
    <div class="container welcome col-11" style="height: auto !important">

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


        <div class="d-flex justify-content-between">
            <div class="col-12">
                <div class="row d-flex " style="direction: rtl">
                    <div class="col-2">
                        <p> بدل الحجز</p>
                    </div>
                    <form class="" action="{{ route('reservation_allowances.search_employee_new') }}" method="post">
                        @csrf
                        <div class="row d-flex flex-wrap ">
                            <!-- 1 for sector , 2 for department -->
                            <input name="department_type" id="department_type" type="hidden"
                                value="{{ Auth::user()->department_id == null ? 1 : 2 }}">

                            <div class="d-flex">
                                {{-- @if (Auth::user()->hasPermission('create reservation_allowances')) --}}
                                <!-- <label for="Civil_number" class="d-flex "> <i class="fa-solid fa-asterisk" style="color:red; font-size:10px;"></i>اختار </label> -->
                                <select class="custom-select custom-select-lg select2" name="sector_id" id="sector_id"
                                    required>
                                    <option value="0" selected>اختار القطاع</option>
                                    @foreach ($sectors as $sector)
                                    <option value="{{ $sector->id }}" {{ $sector->id == $sector_id ? 'selected' : '' }}>
                                        {{ $sector->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="d-flex mx-2">
                                {{-- @if (Auth::user()->hasPermission('create reservation_allowances')) --}}
                                <!-- <label for="Civil_number" class="w-75"> <i class="fa-solid fa-asterisk" style="color:red; font-size:10px;"></i>اختار الادارة</label> -->
                                <select class="custom-select custom-select-lg select2" name="departement_id"
                                    id="departement_id">
                                    <option value="0" selected>اختار الادارة</option>
                                    @if ($get_departements)
                                    @foreach ($get_departements as $departement)
                                    <option value="{{ $departement->id }}"
                                        {{ $departement_id == $departement->id ? 'selected' : '' }}>
                                        {{ $departement->name }}</option>
                                    @if (count($departement->children))
                                    @include('reservation_allowance.manageChildren', [
                                    'children' => $departement->children,
                                    'parent_id' => '',
                                    ])
                                    @endif
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="d-flex">

                                <div class="form-group  mx-3">
                                    <input class="form-control" type="date" name="date" id="date" max="{{$today}}"
                                        value="{{$today}}" required>
                                </div>

                                <div class="d-flex mx-2">
                                    <label for="Civil_number">
                                        <button class="btn-all py-2 px-2" type="submit" style="color:green;">
                                            <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">
                                            اضافة بدل حجز اختياري
                                        </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <hr /><br />
                    <div class="d-flex justify-content-between mt-2">
                        <!-- <div class=" mx-2">
                                    {{-- @if (Auth::user()->hasPermission('create reservation_allowances')) --}}
                                    <a class="btn-all py-2 px-2 " href="{{ route('reservation_allowances.create') }}"
                                        style="color: #0D992C;">
                                        <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">
                                        اضافة بدل حجز جديد
                                    </a>
                                    {{-- @endif --}}
                                </div>-->

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
<<<<<<< HEAD
=======

>>>>>>> d95b51738c0238599046dd4bf2382fe249956480

        <div class="col-lg-12">
            <div class="bg-white">
                @if (session()->has('message'))
                <div class="alert alert-info">
                    {{ session('message') }}
                </div>
                @endif

                <div>
                    <table id="users-table"
                        class="display table table-responsive-sm  table-bordered table-hover dataTable"
                        style="direction:rtl">
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
                        @if ($employees)

                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th>
                                <div class="d-flex" style="justify-content: space-around !important">
                                    <div style="display: inline-flex; direction: ltr;">
                                        <label for=""> حجز كلى</label>
                                        <input type="radio" name="allowance_all" id="allowance_all"
                                            onclick="check_all(1)" value="1" class="form-control">
                                    </div>
                                    <span>|</span>
                                    <div style="display: inline-flex; direction: ltr;">
                                        <label for=""> حجز جزئى</label>
                                        <input type="radio" name="allowance_all" id="allowance_all"
                                            onclick="check_all(2)" value="2" class="form-control">
                                    </div>
                                    <span>|</span>
                                    <div style="display: inline-flex; direction: ltr;">
                                        <label for=""> لا يوجد</label>
                                        <input type="radio" name="allowance_all" id="allowance_all"
                                            onclick="check_all(0)" value="0" checked class="form-control">
                                    </div>
                                </div>
                            </th>
                            <!-- <th style="width:150px;">العمليات</th>-->
                        </tr>

                        @php($x=0)
                        @foreach ($employees as $k_employee=>$employee)
                        @php($x++)
                        <tr>
                            <th>{{ $employee->id }}</th>
                            <th>{{ $employee->name }}</th>
                            <th>{{ $employee->grade_id != null ? $employee->grade->name : 'لا يوجد رتبة' }}</th>
                            <th>{{ $employee->file_number != null ? $employee->file_number : 'لا يوجد رقم ملف' }}
                            </th>
                            <th>
                                <div class="d-flex" style="justify-content: space-around !important">
                                    <div style="display: inline-flex; direction: ltr;">
                                        <label for=""> حجز كلى</label>
                                        <input type="radio" name="allowance[][{{ $employee->id }}]"
                                            id="allowance_all_{{ $x }}" onclick="add_to_cache(1, {{ $employee->id }})"
                                            value="{{ $employee->id }}" class="form-control emlpoyee_allowance_radio">
                                    </div>
                                    <span>|</span>
                                    <div style="display: inline-flex; direction: ltr;">
                                        <label for=""> حجز جزئى</label>
                                        <input type="radio" name="allowance[][{{ $employee->id }}]"
                                            id="allowance_part_{{ $x }}" onclick="add_to_cache(2, {{ $employee->id }})"
                                            value="{{ $employee->id }}" class="form-control emlpoyee_allowance_radio">
                                    </div>
                                    <span>|</span>
                                    <div style="display: inline-flex; direction: ltr;">
                                        <label for=""> لا يوجد</label>
                                        <input type="radio" name="allowance[][{{ $employee->id }}]"
                                            id="allowance_no_{{ $x }}" onclick="add_to_cache(0, {{ $employee->id }})"
                                            value="{{ $employee->id }}" checked
                                            class="form-control emlpoyee_allowance_radio">
                                    </div>
                                </div>
                            </th>
                            <!-- <th style="width:150px;">العمليات</th>-->
                        </tr>
                        @endforeach
                        @endif
                    </table>
                </div>

                <div class="" style="margin-top:20px">
                    <button class="btn-all py-2 px-2" style="color:#ffffff; background-color:#274373 !important;" onclick="confirm_reservation()"
                        class="menu-link px-3">
                     
                        اضف بدل حجز</button>
                </div>

            </div>
        </div>

    </div>

</div>





@endsection

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@push('scripts')
<script>
$(document).ready(function() {
    function closeModal() {
        $('#delete').modal('hide');
    }

    $('#closeButton').on('click', function() {
        closeModal();
    });


    $(document).on("change", "#sector_id", function() {
        var sectorid = this.value;
        var department_type = document.getElementById('department_type').value;
        var map_url = "{{ route('reservation_allowances.get_departement', ['id', 'type']) }}";
        map_url = map_url.replace('id', sectorid);
        map_url = map_url.replace('type', department_type);
        $.get(map_url, function(data) {
            $("#departement_id").html(data);
        });
    });


<<<<<<< HEAD
        function confirm_reservation() {
            Swal.fire({
                title: 'تحذير',
                text: 'هل انت متاكد من انك تريد ان تضيف بدل حجز لهؤلاء الموظفين',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'نعم, نقل',
                cancelButtonText: 'إلغاء',
                confirmButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {  
                    var reservation_date = document.getElementById('date').value;
                    var map_url = "{{ route('reservation_allowances.confirm_reservation_allowances','date') }}";
                    map_url = map_url.replace('date', reservation_date);
                    window.location.href = map_url;
                } else {

                }
            });
        }


        $(function() {
            $(".select2").select2({
                dir: "rtl"
            });
=======
    $(document).on("click", "#sector_id00", function() {
        var sectorid = this.value;
        var department_type = document.getElementById('department_type').value;
        var map_url = "{{ route('reservation_allowances.get_departement', ['id', 'type']) }}";
        map_url = map_url.replace('id', sectorid);
        map_url = map_url.replace('type', department_type);
        $.get(map_url, function(data) {
            $("#departement_id").html(data);
>>>>>>> d95b51738c0238599046dd4bf2382fe249956480
        });
    });


});

function add_to_cache($type, $id) {
    var department_type = document.getElementById('department_type').value;
    var map_url = "{{ route('reservation_allowances.add_reservation_allowances_employess', ['type', 'id']) }}";
    map_url = map_url.replace('id', $id);
    map_url = map_url.replace('type', $type);
    $.get(map_url, function(data) {});
}

function check_all($type) {
    var employee_count = '{{count($employees)}}';
    var type_name = "part";
    if ($type == 1) {
        var type_name = "all";
    } else if ($type == 2) {
        var type_name = "part";
    } else {
        var type_name = "no";
    }


        function confirm_reservation() {
            Swal.fire({
                title: 'تحذير',
                text: 'هل انت متاكد من انك تريد ان تضيف بدل حجز لهؤلاء الموظفين',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'نعم, نقل',
                cancelButtonText: 'إلغاء',
                confirmButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {  
                    var reservation_date = document.getElementById('date').value;
                    var map_url = "{{ route('reservation_allowances.confirm_reservation_allowances','date') }}";
                    map_url = map_url.replace('date', reservation_date);
                    window.location.href = map_url;
                } else {

                }
            });

        }
    }

    //add to chche
    $('.emlpoyee_allowance_radio:checked').each(function(i) {
        var map_url =
            "{{ route('reservation_allowances.add_reservation_allowances_employess', ['type', 'id']) }}";
        map_url = map_url.replace('id', $(this).val());
        map_url = map_url.replace('type', $type);
        $.get(map_url, function(data) {});
    });
}


function confirm_reservation() {
    if (confirm("هل انت متاكد من انك تريد ان تضيف بدل حجز لهؤلاء الموظفين") == true) {
        var reservation_date = document.getElementById('date').value;
        var map_url = "{{ route('reservation_allowances.confirm_reservation_allowances','date') }}";
        map_url = map_url.replace('date', reservation_date);
        $.get(map_url, function(data) {
            window.location.href = "{{route('reservation_allowances.index')}}";
        });
    }
}


$(function() {
    $(".select2").select2({
        dir: "rtl"
    });
});
</script>
@endpush