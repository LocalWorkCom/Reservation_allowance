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
                <div class=" d-flex flex-wrap justify-content-between " style="direction: rtl">
                    <div>
                        <p> بدل حجز بالهويات</p>
                    </div>

                    <form class="" id="search_employee_allowances">
                        @csrf
                        <div class="row d-flex flex-wrap">
                            <!-- 1 for sector , 2 for department -->
                            <input name="department_type" id="department_type" type="hidden"
                                value="{{ Auth::user()->department_id == null ? 1 : 2 }}">

                            <div class="mx-1">
                                {{-- @if (Auth::user()->hasPermission('create reservation_allowances')) --}}
                                <!-- <label for="Civil_number" class="d-flex "> <i class="fa-solid fa-asterisk" style="color:red; font-size:10px;"></i>اختار </label> -->
                                <select class="custom-select custom-select-lg select2" name="sector_id" id="sector_id"
                                    required>
                                    <option value="0" selected>اختار القطاع</option>
                                    @foreach ($sectors as $sector)
                                    <option value="{{ $sector->id }}" {{$sector->id == $sector_id ? "selected" : ""}}>
                                        {{ $sector->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class=" mx-1" id="departement_div">
                                {{-- @if (Auth::user()->hasPermission('create reservation_allowances')) --}}
                                <!-- <label for="Civil_number" class="w-75"> <i class="fa-solid fa-asterisk" style="color:red; font-size:10px;"></i>اختار الادارة</label> -->
                                <select class="custom-select custom-select-lg" name="departement_id"
                                    id="departement_id">
                                    <option value="0">اختار الادارة</option>
                                    @if($get_departements)
                                    @foreach($get_departements as $departement)
                                    <option value="{{ $departement->id }}"
                                        {{$departement->id == $department_id ? "selected" : ""}}>
                                        {{ $departement->name }}</option>
                                    @if(count($departement->children))
                                    @include('reservation_allowance.manageChildren', [
                                    'children' => $departement->children,
                                    'parent_id' => $department_id,
                                    ])
                                    @endif
                                    @endforeach
                                    @endif

                                </select>
                            </div>

                            <div class="form-group  mx-2">
                                <input class="form-control" type="date" name="date" id="date" max="{{$to_day}}"
                                    value="{{$to_day}}" required>
                            </div>

                            <!-- <div class="">
                                        <button class="btn-all py-2 px-2" type="submit" style="color:green;">
                                            <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">
                                            عرض موظفين بدل حجز </button>
                                </div>-->
                        </div>
                    </form>
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
    <div class="container  col-11 mt-3 p-0  p-4">

    <ul class="nav nav-tabs " id="myTab" role="tablist" dir="rtl">
        <li class="nav-item " role="presentation ">
            <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button"
                    role="tab" aria-controls="home" aria-selected="true">
                الموظفين الذين سيتم اضافتهم
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link " id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button"
                    role="tab" aria-controls="profile" aria-selected="false">
                الموظفين غير مسجلين فى الادارة او القطاع
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link " id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button"
                    role="tab" aria-controls="contact" aria-selected="false">
                موظفين ارقام الملفات خطأ
            </button>
        </li>
    </ul>
    <div class="tab-content mt-3" id="myTabContent">
        <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
            <!-- <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>الترتيب</th>
                        <th>الاسم</th>
                        <th>رقم الملف</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>اسم الموظف</td>
                        <td>123</td>
                    </tr>
                </tbody>
            </table> -->
            <h3 class="text-center text-info"> لا يوجد</h3>
        </div>
        <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>الترتيب</th>
                        <th>الاسم</th>
                        <th>رقم الملف</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>اسم الموظف</td>
                        <td>456</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>الترتيب</th>
                        <th>رقم الملف</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>789</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>


    </div>

</div>

<script>
$(".select2").select2({
    dir: "rtl"
});
$('#sector_id').on('select2:select', function(e) {
    // alert('select');
    // var managerId = $(this).val();
    var sectorid = $(this).val();
    var department_type = document.getElementById('department_type').value;
    var map_url = "{{ route('reservation_allowances.get_departement', ['id', 'type']) }}";
    map_url = map_url.replace('id', sectorid);
    map_url = map_url.replace('type', department_type);
    $.get(map_url, function(data) {
        $("#departement_id").html(data);
        // initDept()
        $('#departement_id').val(0).trigger('change');

    });
});

function initDept() {
    $("#departement_id").select2({
        dir: "rtl"
    });
}
</script>



@endsection
@push('scripts')

<script>
$(document).ready(function() {
    var table = "";
    var sector_id = document.getElementById('sector_id').value;
    var departement_id = document.getElementById('departement_id').value;
    var date = document.getElementById('date').value;
    var filter = 'all'; // Default filter

    table = $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('reservation_allowances.getAll') }}",
            data: function(d) {
                d.filter = filter; // Use the global filter variable
                d.sector_id = sector_id;
                d.departement_id = departement_id;
                d.date = date;
            }
        },
        columns: [{
                data: null,
                name: 'order',
                orderable: false,
                searchable: false
            },
            {
                data: 'employee_grade',
                name: 'employee_grade'
            },
            {
                data: 'employee_name',
                name: 'employee_name'
            },
            {
                data: 'employee_file_num',
                name: 'employee_file_num'
            },
            {
                data: 'employee_allowance_type_btn',
                name: 'employee_allowance_type_btn'
            },
            {
                data: 'employee_allowance_amount',
                name: 'employee_allowance_amount'
            }
        ],
        order: [0, 'asc'],


        layout: {

            bottomEnd: {
                paging: {
                    firstLast: false
                }
            }
        },
        bDestroy: true,
        "oLanguage": {
            "sSearch": "",
            "sSearchPlaceholder": "بحث",
            "sInfo": 'اظهار صفحة _PAGE_ من _PAGES_',
            "sInfoEmpty": 'لا توجد بيانات متاحه',
            "sInfoFiltered": '(تم تصفية  من _MAX_ اجمالى البيانات)',
            "sLengthMenu": 'اظهار _MENU_ عنصر لكل صفحة',
            "sZeroRecords": 'نأسف لا توجد نتيجة',
            "oPaginate": {
                "sFirst": '<i class="fa fa-fast-backward" aria-hidden="true"></i>',
                "sPrevious": '<i class="fa fa-chevron-left" aria-hidden="true"></i>',
                "sNext": '<i class="fa fa-chevron-right" aria-hidden="true"></i>',
                "sLast": '<i class="fa fa-step-forward" aria-hidden="true"></i>'
            }
        },
        "pagingType": "full_numbers",
        "fnDrawCallback": function(oSettings) {
            var api = this.api();
            var pageInfo = api.page.info();
            if (pageInfo.recordsTotal <= 10) {
                $('.dataTables_paginate').css(
                    'visibility', 'hidden');
            } else {
                $('.dataTables_paginate').css(
                    'visibility', 'visible');
            }
        },

        createdRow: function(row, data, dataIndex) {
            $('td', row).eq(0).html(dataIndex + 1); // Automatic numbering in the first column
        }
    });
    $('.btn-filter').on('click', function() {
        filter = $(this).data('filter'); // Get the filter value from the clicked button
        table.ajax.reload(); // Reload the DataTable with the new filter
    });
    // Filter buttons click event
    $('.btn-filter').click(function() {
        filter = $(this).data('filter'); // Update filter
        $('.btn-filter').removeClass('btn-active'); // Remove active class from all
        $(this).addClass('btn-active'); // Add active class to clicked button

        table.page(0).draw(false); // Reset to first page and redraw the table
    });
    //end of call datatable



});
</script>
<script>
$('.c-radio').on('change', function() {
    // Get the selected value
    var selectedValue = $(this).val();
    console.log("Selected option: " + selectedValue);

    // Perform actions based on the selected value
    if (selectedValue === '0') {
        alert("You selected 0");
    } else if (selectedValue === '1') {
        alert("You selected Option 1");
    } else if (selectedValue === '2') {
        alert("You selected Option 2");
    }
});

import {
    Tab,
    initMDB
} from "mdb-ui-kit";

initMDB({
    Tab
});
</script>
@endpush