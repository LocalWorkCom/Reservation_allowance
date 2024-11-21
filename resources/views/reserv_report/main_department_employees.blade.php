@extends('layout.main')

@section('title', "تفاصيل الموظفين المحجوزين في الإدارة الرئيسية: {$department->name}")

@push('style')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js" defer></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer></script>
@endpush

@section('content')
<div class="row" style="direction: rtl;">
    <div class="container welcome col-11">
        <div class="d-flex justify-content-between">
            <p> تفاصيل الموظفين المحجوزين في الإدارة الرئيسية: {{ $department->name }}  الفترة  من : {{ $startDate->format('Y-m-d') }} إلى: {{ $endDate->format('Y-m-d') }}</p>
        </div>
        <button id="print-report" class="btn btn-secondary">طباعة</button>
    </div>
</div>

<div class="row" style="direction: rtl;">
    <div class="container col-11 mt-3 p-0 pt-5 pb-4">
        <table id="users-table" class="display table table-bordered table-hover dataTable">
            <thead>
                <tr>
                    <th>الترتيب</th>
                    <th>الرتبة</th>
                    <th>اسم الموظف</th>
                    <th>رقم الملف</th>
                   
                    <th>عدد الأيام</th>
                    <th>المبلغ الإجمالي</th>
                   
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $.fn.dataTable.ext.classes.sPageButton =
    'btn-pagination btn-sm';
    $('#users-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: '{{ route('reservation_report.main_department_employees_data', ['departmentId' => $department->id]) }}',
        data: {
            start_date: '{{ $startDate->format('Y-m-d') }}',
            end_date: '{{ $endDate->format('Y-m-d') }}'
        }
    },
    columns: [
        { 
            data: null, 
            orderable: false, 
            searchable: false,
            render: function (data, type, row, meta) {
                return meta.row + 1;
            }
        },
        { data: 'grade', name: 'grade' },
        { data: 'name', name: 'name' },
        { data: 'file_number', name: 'file_number',
            render: function(data, type, row) {
                const startDate = '{{ $startDate->format('Y-m-d') }}';
                const endDate = '{{ $endDate->format('Y-m-d') }}';
                return `<a href="/reservation_report/user/${row.id}/details?start_date=${startDate}&end_date=${endDate}" style="color:blue !important;">${data}</a>`;
            }
         },
        { data: 'days', name: 'days',
            render: function(data, type, row) {
                const startDate = '{{ $startDate->format('Y-m-d') }}';
                const endDate = '{{ $endDate->format('Y-m-d') }}';
                return `<a href="/reservation_report/user/${row.id}/details?start_date=${startDate}&end_date=${endDate}" style="color:blue !important;">${data}</a>`;
            }
         },
        { 
            data: 'allowance', 
            name: 'allowance',
            render: function(data, type, row) {
                const startDate = '{{ $startDate->format('Y-m-d') }}';
                const endDate = '{{ $endDate->format('Y-m-d') }}';
                return `<a href="/reservation_report/user/${row.id}/details?start_date=${startDate}&end_date=${endDate}" style="color:blue !important;">${data}</a>`;
            }
        }
    ],
    order: [[1, 'desc']],
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
                if (pageInfo.recordsTotal <= 10) {
                    $('.dataTables_paginate').css('visibility', 'hidden');
                } else {
                    $('.dataTables_paginate').css('visibility', 'visible');
                }
            }
        });



    $('#print-report').click(function() {
        const startDate = '{{ $startDate->format('Y-m-d') }}';
        const endDate = '{{ $endDate->format('Y-m-d') }}';
        const url = `{{ route('reservation_report.main_department_employees_print', ['departmentId' => $department->id]) }}?start_date=${startDate}&end_date=${endDate}`;
        window.open(url, '_blank');
    });
});
</script>
@endpush
