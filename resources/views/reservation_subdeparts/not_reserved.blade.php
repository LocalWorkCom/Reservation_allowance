@extends('layout.main')
@push('style')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js" defer></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer></script>
@endpush
@section('title', 'الموظفين في الادارة')

@section('content')
<div class="row">
    <div class="container welcome col-11">
        <div class="d-flex justify-content-between">
            <p>الموظفين الغير حاصلين على بدل حجز  في الادارة: {{ $subDepartmentName }}</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="container col-11 mt-3 p-0 pt-5 pb-4">
    <div class="d-flex justify-content-end px-3" >
                <h4 class="px-2">الشهر: <span class="text-info">{{ $month }}</span></h4>
                <h4 class="px-2">السنة: <span class="text-info">{{ $year }}</span></h4>
            </div>
        <div class="bg-white p-4">
            <table id="users-table" class="display table table-bordered table-hover dataTable">
                <thead>
                    <tr>
                        <th>الترتيب</th>
                        <th>الرتبة</th>
                        <th>اسم الموظف</th>
                        <th>رقم الملف</th>
                       
                       
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
    $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("subdepartment.not_received.data", $subDepartmentId) }}',
            data: function(d) {
                d.month = '{{ $month }}';
                d.year = '{{ $year }}';
            }
        },
        columns: [
            { data: null, orderable: false, searchable: false, render: function(data, type, row, meta) {
                return meta.row + 1; // Row number
            }},
            { data: 'grade', name: 'grade' },
            { data: 'name', name: 'name' },
            { data: 'file_number', name: 'file_number' },
        
          
        ],
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
                    var api = this.api();
                    var pageInfo = api.page.info();
                    // Check if the total number of records is less than or equal to the number of entries per page
                    if (pageInfo.recordsTotal <= 10) { // Adjust this number based on your page length
                        $('.dataTables_paginate').css('visibility', 'hidden'); // Hide pagination
                    } else {
                        $('.dataTables_paginate').css('visibility', 'visible'); // Show pagination
                    }
                }
        });
});

    
    </script>
@endpush