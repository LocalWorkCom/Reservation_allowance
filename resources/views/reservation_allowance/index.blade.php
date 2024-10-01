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
                    @if(auth()->user()->department->children->count() <= 0 && auth()->user()->rule_id == 3)
                        <div class="col-8">
                            <p> بدل الحجز</p>
                        </div>
                        <div class="col-2">{{-- @if (Auth::user()->hasPermission('create reservation_allowances')) --}}
                            <a class="btn-all" href="{{ route('reservation_allowances.create') }}"
                                style="color: #0D992C;">
                                <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">

                                اضافة بدل حجز جديد
                            </a>
                            {{-- @endif --}}
                        </div>
                        <div class="col-2">{{-- @if (Auth::user()->hasPermission('create reservation_allowances')) --}}
                            <a class="btn-all" href="{{ route('reservation_allowances.create.all') }}"
                                style="color: #0D992C;">
                                <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">

                                اضافة بدل حجز كلى جديد
                            </a>
                            {{-- @endif --}}
                        </div>
                        @endif


                        @if(auth()->user()->rule_id != 2)
                        <div class="col-12 div-info d-flex justify-content-between">
                            <div class="col-7">
                                <div class="col-6 div-info-padding"><b>القطاع : <span
                                            style="color:#032F70;">{{auth()->user()->department->sectors->name}}</span></b>
                                </div>
                                <div class="col-6 div-info-padding"><b>الادارة الفرعية : <span
                                            style="color:#032F70;">{{auth()->user()->department->name}}</span></b></div>
                                <div class="col-6 div-info-padding"><b>اليوم : <span style="color:#032F70;">
                                            {{$to_day_name}}</span></b></div>
                                <div class="col-6 div-info-padding"><b>عدد العسكرين المحجوزين : <span
                                            style="color:#032F70;">{{$reservation_allowances->count()}}</span></b></div>
                            </div>
                            <div class="col-5">
                                <div class="col-6 div-info-padding"><b>الادارة الرئيسية : <span
                                            style="color:#032F70;">{{auth()->user()->department->parent == null ? $super_admin->name : auth()->user()->department->parent->name}}</span></b>
                                </div>
                                <div class="col-6 div-info-padding"><b>مبلغ بدل الحجز : <span
                                            style="color:#032F70;">{{auth()->user()->department->reservation_allowance_amount}}</span></b>
                                </div>
                                <div class="col-6 div-info-padding"><b>التاريخ : <span style="color:#032F70;">
                                            {{$to_day}}</span></b></div>
                            </div>
                        </div>
                        @endif

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
                                <th>الرتيب</th>
                                <th>الرتبة</th>
                                <th>الاسم</th>
                                <th>رقم الملف</th>
                                <th>بدل الحجز</th>
                                <th>اليومية</th>
                                <!-- <th style="width:150px;">العمليات</th>-->
                            </tr>
                        </thead>
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


});


$(function() {
    $(".select2").select2({
        dir: "rtl"
    });
});
</script>
</script>
<script>
function opendelete(id) {
    document.getElementById('id').value = id;
    $('#delete').modal('show');
}

function confirmDelete() {
    var id = document.getElementById('id').value;
    var form = document.getElementById('delete-form');
    form.submit();
}
$(document).ready(function() {
    // Check if there are errors
    @if($errors - > any())
    // Check if it's an add or edit operation
    @if(session('modal_type') === 'add')
    $('#addForm').modal('show');
    @elseif(session('modal_type') === 'edit')
    $('#edit').modal('show');
    @endif
    @endif
});

function openedit(id, name, type, value_all, value_part, order) {
    document.getElementById('nameedit').value = name;
    document.getElementById('idedit').value = id;
    document.getElementById('typeedit').value = type; // Set the value for type
    document.getElementById('value_alledit').value = value_all; // Set value_all
    document.getElementById('value_partedit').value = value_part; // Set value_part
    document.getElementById('orderedit').value = order; // Set value_part

    $('#edit').modal('show');
}

function confirmEdit() {
    var id = document.getElementById('id').value;
    var name = document.getElementById('nameedit').value;
    console.log(name);
    var form = document.getElementById('edit-form')
}

function addForm() {
    $('#addForm').modal('show');
}



function confirmAdd() {
    var name = document.getElementById('nameadd').value;
    var idedit = document.getElementById('idedit').value;
    var value_part = document.getElementById('value_part').value;
    var value_all = document.getElementById('value_all').value;
    var order = document.getElementById('order').value;

    var form = document.getElementById('add-form');
    var inputs = form.querySelectorAll('[required]');
    var valid = true;

    inputs.forEach(function(input) {
        if (!input.value) {
            valid = false;
            input.style.borderColor = 'red'; // Optional: highlight empty inputs
        } else {
            input.style.borderColor = ''; // Reset border color if input is filled
        }
    });

    if (valid) {
        form.submit();
    }
}

$(document).ready(function() {
    $.fn.dataTable.ext.classes.sPageButton = 'btn-pagination btn-sm'; // Change Pagination Button Class

    var filter = 'all'; // Default filter

    const table = $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('
            reservation_allowances.getAll ') }}',
            data: function(d) {
                d.filter = filter; // Use the global filter variable
            }
        },
        columns: [{
                data: 'id',
                name: 'id'
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
            <?php /*{
                        data: 'action',
                        name: 'action',
                        sWidth: '100px',
                        orderable: false,
                        searchable: false
                    }*/?>
        ],
        "order": [0, 'asc'],

        "oLanguage": {
            "sSearch": "",
            "sSearchPlaceholder": "بحث",
            "sInfo": 'اظهار صفحة _PAGE_ من _PAGES_',
            "sInfoEmpty": 'لا توجد بيانات متاحه',
            "sInfoFiltered": '(تم تصفية  من _MAX_ اجمالى البيانات)',
            "sLengthMenu": 'اظهار _MENU_ عنصر لكل صفحة',
            "sZeroRecords": 'نأسف لا توجد نتيجة',
            "oPaginate": {
                "sFirst": '<i class="fa fa-fast-backward" aria-hidden="true"></i>', // This is the link to the first page
                "sPrevious": '<i class="fa fa-chevron-left" aria-hidden="true"></i>', // This is the link to the previous page
                "sNext": '<i class="fa fa-chevron-right" aria-hidden="true"></i>', // This is the link to the next page
                "sLast": '<i class="fa fa-step-forward" aria-hidden="true"></i>' // This is the link to the last page
            }
        },
        layout: {

            bottomEnd: {
                paging: {
                    firstLast: false
                }
            }
        },
        "pagingType": "full_numbers",
        "fnDrawCallback": function(oSettings) {
            console.log('Page ' + this.api().page.info().pages)
            var page = this.api().page.info().pages;
            console.log($('#users-table tr').length);
            if (page <= 1) {
                //$('.dataTables_paginate').hide();//css('visiblity','hidden');
                $('.dataTables_paginate').css('visibility', 'hidden'); // to hide
            }
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
});
</script>
@endpush