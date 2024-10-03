@extends('layout.main')

@push('style')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
<script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js" defer></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer>
</script>

<style>
.div-info {
    border-radius: 10px;
    padding: 20px;
    margin-top: 20px;
    width: 100%;
    background-color: #F6F7FD;
    border: 1px solid #D9D9D9 !important;
}

.div-info-padding {
    padding: 3px 0;
    font-family: Almarai;
    font-size: 24px;
    font-weight: 700;
    line-height: 36px;
    text-align: right;
}

.div-info-padding b span {
    color: #032F70;
}

/* Box around the table */
.table-box {
    margin-top: 30px;
    padding: 20px;
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0px 2px 15px rgba(0, 0, 0, 0.1);
}

h3 {
    font-family: Almarai;
    font-size: 22px;
    text-align: center;
    color: #333;
}

#search-form input {
    height: 40px;
    border-radius: 10px !important;
    background-color: #f5f6fa;
}
#search-form label{
font-size:20px;
margin-inline:10px;
font-weight:700;
}
</style>
@endpush

@section('title')
البحث عن بيانات الحجز
@endsection

@section('content')
<div class="container welcome col-11 my-4" dir="rtl">
    <div class="d-flex justify-content-between">
        <!-- Search Form -->
        <form id="search-form" class="row col-12">
            <div class=" d-flex">
                <label for="civil_number " class="d-flex w-50">رقم الهوية</label>
                <input type="text" id="civil_number" name="civil_number" class="form-control" required>
            </div>
            <div class=" d-flex">
                <label for="start_date">من </label>
                <input type="date" id="start_date" name="start_date" class="form-control">
            </div>
            <div class=" d-flex">
                <label for="end_date">الى </label>
                <input type="date" id="end_date" name="end_date" class="form-control">
            </div>
            <div class=" ">
                <button type="submit" class="btn mx-2" style="background-color: #274373; color:white;">بحث</button>
            </div>
        </form>
    </div>
</div>
<!-- Results Table -->
<div class="container  col-11">
    <div class="">
        <h3>نتائج البحث</h3>
        <div class="col-md-2 mb-2">
            <button type="button" class="btn " onclick="printPDF()"  style="background-color: #274373; color:white;">طباعة</button>
        </div>
        <table id="reservation-table" class="display table table-responsive-sm table-bordered table-hover dataTable">
            <thead>
                <tr>
                    <th>الترتيب</th>
                    <th>اليوم</th>
                    <th>التاريخ</th>
                    <th>الاسم</th>
                    <th>الادارة</th>
                    <th>نوع الحجز</th>
                    <th>القيمة</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var table = null;

    // Function to initialize the DataTable
    function initializeTable() {
        table = $('#reservation-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("reservation_fetch.getAll") }}',
                data: function(d) {
                    d.civil_number = $('#civil_number').val().trim();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                },
                error: function(xhr, error, code) {
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        alert(xhr.responseJSON.error); // Show the error if no user is found
                    }
                }
            },
            columns: [{
                    data: null,
                    name: 'order',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'day',
                    name: 'day'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'department',
                    name: 'department'
                },
                {
                    data: 'type',
                    name: 'type'
                },
                {
                    data: 'amount',
                    name: 'amount'
                }
            ],
            order: [
                [2, 'asc']
            ],
            "oLanguage": {
                "sSearch": "",
                "sSearchPlaceholder": "بحث",
                "sInfo": 'اظهار صفحة _PAGE_ من _PAGES_',
                "sInfoEmpty": 'لا توجد بيانات متاحه',
                "sInfoFiltered": '(تم تصفية  من _MAX_ اجمالى البيانات)',
                "sLengthMenu": 'اظهار _MENU_ عنصر لكل صفحة',
                "sZeroRecords": 'نأسف لا توجد نتيجة مطابقة',
                "sEmptyTable": 'لا توجد بيانات متاحة',
                "oPaginate": {
                    "sFirst": '<i class="fa fa-fast-backward" aria-hidden="true"></i>',
                    "sPrevious": '<i class="fa fa-chevron-left" aria-hidden="true"></i>',
                    "sNext": '<i class="fa fa-chevron-right" aria-hidden="true"></i>',
                    "sLast": '<i class="fa fa-step-forward" aria-hidden="true"></i>'
                }
            },
            pagingType: "full_numbers",
            fnDrawCallback: function(oSettings) {
                var page = this.api().page.info().pages;
                if (page == 1) {
                    $('.dataTables_paginate').css('visibility', 'hidden');
                }
            },
            createdRow: function(row, data, dataIndex) {
                $('td', row).eq(0).html(dataIndex + 1); // Automatic numbering in the first column
            }
        });
    }

    // Trigger the table reload on search form submission
    $('#search-form').on('submit', function(e) {
        e.preventDefault();
        var civil_number = $('#civil_number').val().trim();

        if (civil_number !== '') {
            if (table === null) {
                initializeTable(); // Initialize DataTable if not initialized
            } else {
                table.draw(); // Reload table only if civil_number is provided
            }
        } else {
            alert('Please enter a valid Civil Number');
        }
    });
});



function printPDF() {
    let civil_number = $('#civil_number').val();
    let start_date = $('#start_date').val();
    let end_date = $('#end_date').val();

    // Redirect to the print route with search parameters
    window.open('{{ route("reservation_fetch.print") }}' + '?civil_number=' + civil_number + '&start_date=' +
        start_date + '&end_date=' + end_date, '_blank');
}
</script>
@endpush