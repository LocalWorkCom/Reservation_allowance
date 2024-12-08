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

<div class="row" dir="rtl">
    <div class="container col-11" style="background-color:transparent;">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('reservation_allowances.create.all') }}">بدل حجز بالهويات</a></li>
            </ol>
        </nav>
        
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

    </div>
</div>

<div class="row">
    <div class="container welcome col-11" style="height: auto !important">
        <div class="d-flex justify-content-between">
            <div class="col-12">
                <div class=" d-flex flex-wrap justify-content-between " style="height: 40px;direction: rtl">
                    <div>
                        <p> بدل حجز بالهويات</p>
                    </div>
                    <?php /*@if(Cache::get(auth()->user()->id."_employee_new_add") != null)*/?>
                <!-- <div class="col-lg-12" style="text-align: right"> -->
                    <form method="post" action="{{ route('reservation_allowances.store.all') }}">
                        @csrf
                        <input type="hidden" name="date" value="{{$to_day}}">
                        <input type="hidden" name="type" value="{{$type}}">
                        <input type="hidden" name="sector_id" value="{{$sectorId}}">
                        <input type="hidden" name="departement_id" value="{{$departmentId}}">
                        <button class="btn btn-blue py-2 px-3 mx-2" type="submit">اعتماد الكشف</button>
                        <button class="btn btn-blue py-2 px-3" onclick="history.back()" type="button" style="background-color:red;">الغاء</button>
                        </from>
                <!-- </div> -->
                <?php /*@endif*/?>
                  
            </div>
        </div>
    </div>
</div> 
          

       

   



<div class="container  col-11 mt-3 p-0  pt-5 pb-4" dir="rtl">
        
            <div class=" col-12 d-flex flex-wrap mb-4  ">
                @if($current_sector)
                <h5 class="text-dark mx-md-3">القطاع : <span class="text-info">{{$current_sector->name}}</span></h5>
                @endif
                @if($current_departement)
                <h5 class="text-darkmx-md-3">الادارة : <span class="text-info">{{$current_departement->name}}</span></h5>
                @endif
                <h5 class="text-dark mx-md-3">التاريخ : <span class="text-info">{{$to_day}}</span></h5>
                <h5 class="text-dark mx-md-3">القوة : <span class="text-info">{{count($employee_new_add)}}</span></h5>
                
               
            </div>
 

        <ul class="nav nav-tabs " id="myTab" role="tablist">
            <li class="nav-item " role="presentation ">
                <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button"
                    role="tab" aria-controls="home" aria-selected="true">
                    الموظفين الذين سيتم اضافتهم ( {{ $employee_new_add ? count($employee_new_add) : 0}} )
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link " id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button"
                    role="tab" aria-controls="profile" aria-selected="false">
                   الموظفين غير مسجلين فى الادارة او القطاع ( {{ $employee_not_dept ? count($employee_not_dept) : 0}} )
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link " id="existing-tab" data-bs-toggle="tab" data-bs-target="#existing" type="button"
                    role="tab" aria-controls="existing" aria-selected="false">
                   موظفين لديهم بدل حجز اليوم ( {{ $employee_existing ? count($employee_existing) : 0}} )
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link " id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button"
                    role="tab" aria-controls="contact" aria-selected="false">
                   موظفين ارقام الملفات خطأ ( {{ $employee_not_found ? count($employee_not_found) : 0}} )
                </button>
            </li>
            
        </ul>

        <div class="tab-content mt-3" id="myTabContent">
            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                @if($employee_new_add)
                <table class="table table-bordered ">
                    <thead>
                        <tr>
                            <th style="width:5%">م</th>
                            <th>الرتبة</th>
                            <th>الاسم</th>
                            <th>رقم الملف</th>
                            <th>التكلفة</th>
                            <th>الادارة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employee_new_add as $K_employee_newadd=>$employee_newadd)
                        <tr >
                            <td class="text-dark fw-bolder">{{$K_employee_newadd+1}}</td>
                            <td class="text-dark fw-bolder">{{$employee_newadd->grade != null ? $employee_newadd->grade->name : ""}}</td>
                            <td class="text-dark fw-bolder">{{$employee_newadd->name}}</td>
                            <td class="text-dark fw-bolder">{{$employee_newadd->file_number}}</td>
                            <td class="text-dark fw-bolder">{{$employee_newadd->grade_value}}</td>
                            <td class="text-dark fw-bolder">{{$employee_newadd->department_id != null ? $employee_newadd->department->name : ""}}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <h3 class="text-center text-info"> لا يوجد بيانات</h3>
                @endif
            </div>

            <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                @if($employee_not_dept)
                <table class="table table-bordered ">
                    <thead>
                        <tr >
                            <th style="width:5%">م</th>
                            <th>الرتبة</th>
                            <th>الاسم</th>
                            <th>رقم الملف</th>
                            <th>الادارة</th>
                            <th>بدل حجز</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employee_not_dept as $K_employee_notdept=>$employee_notdept)
                        <tr class="text-dark">
                            <td class="text-dark fw-bolder">{{$K_employee_notdept+1}}</td>
                            <td class="text-dark fw-bolder">{{$employee_notdept->grade != null ? $employee_notdept->grade->name : ""}}</td>
                            <td class="text-dark fw-bolder">{{$employee_notdept->name}}</td>
                            <td class="text-dark fw-bolder">{{$employee_notdept->file_number}}</td>
                            <td class="text-dark fw-bolder">{{$employee_notdept->department_id != null ? $employee_notdept->department->name : ""}}</td>
                            <td class="text-dark fw-bolder">
                            <a href="#" id="add_reservation_but" onclick="add_reservation_to_employee('{{$employee_notdept->uuid}}')">اضف بدل حجز</a>
                            <a href="#" id="done_reservation_but" style="display:none">تم اضافة بدل حجز</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <h3 class="text-center text-info"> لا يوجد بيانات</h3>
                @endif
            </div>

            <div class="tab-pane fade" id="existing" role="tabpanel" aria-labelledby="existing-tab">
                @if($employee_existing)
                <table class="table table-bordered ">
                    <thead>
                        <tr >
                            <th style="width:5%">م</th>
                            <th>الرتبة</th>
                            <th>الاسم</th>
                            <th>رقم الملف</th>
                            <th>الادارة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employee_existing as $K_employee_existing=>$employee_existing)
                        <tr class="text-dark">
                            <td class="text-dark fw-bolder">{{$K_employee_existing+1}}</td>
                            <td class="text-dark fw-bolder">{{$employee_existing->grade != null ? $employee_existing->grade->name : ""}}</td>
                            <td class="text-dark fw-bolder">{{$employee_existing->name}}</td>
                            <td class="text-dark fw-bolder">{{$employee_existing->file_number}}</td>
                            <td class="text-dark fw-bolder">{{$employee_existing->department_id != null ? $employee_existing->department->name : ""}}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <h3 class="text-center text-info"> لا يوجد بيانات</h3>
                @endif
            </div>

            <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                @if($employee_not_found)
                <table class="table table-bordered ">
                    <thead>
                        <tr>
                            <th style="width:5%">م</th>
                            <th>رقم الملف</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employee_not_found as $K_employee_notfound=>$employee_notfound)
                        <tr class="text-dark">
                            <td class="text-dark fw-bolder">{{$K_employee_notfound+1}}</td>
                            <td class="text-dark fw-bolder">{{$employee_notfound['Civil_number']}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <h3 class="text-center text-info"> لا يوجد بيانات</h3>
                @endif
            </div>
        </div>

        </div>
            </div>

        </div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

function add_reservation_to_employee($employee)
{
    Swal.fire({
        title: 'تنبيه',
        text: 'هل انت متاكد من انك تريد ان تضيف بدل حجز لهذا الموظف',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'نعم, اعتمد',
        cancelButtonText: 'إلغاء',
        confirmButtonColor: '#3085d6'
    }).then((result) => {
        if (result.isConfirmed) {
            //window.location.href = map_url;
            var map_url = "{{ route('reservation_allowances.add_reservation_allowances_employes_id', ['uuid']) }}";
            map_url = map_url.replace('uuid', $employee);
            $.get(map_url, function(data) {});
            document.getElementById("add_reservation_but").style.display = 'none';
            document.getElementById("done_reservation_but").style.display = 'block';
        } else {

        }
    });
}

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

@endpush