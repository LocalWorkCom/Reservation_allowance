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
                <div class=" d-flex flex-wrap justify-content-between " style="height: 40px;direction: rtl">
                    <div>
                        <p> بدل الحجز</p>
                    </div>

                    <input type="hidden" name="sector_id" id="sector_id" value="{{$sector_id}}">
                    <input type="hidden" name="departement_id" id="departement_id" value="{{$departement_id}}">
                    <input class="form-control" type="hidden" name="date" id="date" readonly value="{{$to_day}}">
                    <input class="form-control" type="hidden" readonly name="sector"
                        value="{{$current_departement ? $current_departement->name : 'لا يوجد ادارة'}}">
                    <input class="form-control" type="hidden" readonly name="departement"
                        value="{{$current_sector ? $current_sector->name : 'لا يوجد قطاع'}}">
                    <input name="department_type" id="department_type" type="hidden"
                        value="{{ Auth::user()->department_id == null ? 1 : 2 }}">
                    <div class="d-flex">
                        <!-- 1 for sector , 2 for department -->




                        <h5 class="btn-all px-2 p-1 mx-1">{{$current_sector ? $current_sector->name : 'لا يوجد قطاع'}}
                        </h5>

                        <h5 class="btn-all px-2 p-1 mx-1">
                            {{$current_departement ? $current_departement->name : 'لا يوجد ادارة'}}</h5>

                        <h5 class="btn-all px-2 p-1 mx-1">{{$to_day}}</h5>
                    </div>

                </div>
            </div>

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
                    <table id="users-table"
                        class="display table table-responsive-sm  table-bordered table-hover dataTable">
                        <thead>
                            <tr>
                                <th style="width:5%">م</th>
                                <th>الرتبة</th>
                                <th>الاسم</th>
                                <th>رقم الملف</th>
                                <th>نوع بدل الحجز</th>
                                <th>نوع بدل الحجز</th>
                                <th>اليومية</th>
                                <th>ملاحظات</th>
                                <!-- <th style="width:150px;">العمليات</th>-->
                            </tr>
                        </thead>
                    </table>
                </div>
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
    $.fn.dataTable.ext.classes.sPageButton =
        'btn-pagination btn-sm'; // Change Pagination Button Class
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
                data: 'employee_allowance_all_btn',
                name: 'employee_allowance_all_btn'
            },
            {
                data: 'employee_allowance_part_btn',
                name: 'employee_allowance_part_btn'
            },
            {
                data: 'employee_allowance_amount',
                name: 'employee_allowance_amount'
            },
            {
                data: 'notes',
                name: 'notes'
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
</script>
@endpush