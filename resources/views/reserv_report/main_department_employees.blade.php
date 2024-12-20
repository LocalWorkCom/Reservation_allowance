@extends('layout.main')

@section('title', "تفاصيل الموظفين المحجوزين في الإدارة الرئيسية: {$department->name}")

@push('style')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js" defer></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer></script>
@endpush

@section('content')
<div class="row" >
        <div class="container welcome col-11">
            <div class="d-flex justify-content-between">
            <p> تفاصيل الموظفين المحجوزين في الإدارة : {{ $department->name }}     </p>
        </div>
        
    </div>
</div>

<div class="container col-11 mt-3 py-5  " >
<div class="d-flex justify-content-between pb-3"dir="rtl">
<h4> الفترة من: <span class="text-info">{{ $startDate->format('Y-m-d') }}</span> إلى: <span class="text-info">{{ $endDate->format('Y-m-d') }}</span></h4>
<button id="print-report" class="btn-blue">طباعة</button>
</div>
<table id="users-table" class="display table table-bordered table-hover dataTable">
    <thead>
    <tr>
            <!-- <th colspan="5"></th> -->
            <th rowspan="2">الترتيب</th>
            <th rowspan="2">الرتبة</th>
            <th rowspan="2">الاسم</th>
            <th rowspan="2">رقم الملف</th>
            <th colspan="3"> ايام الحجز </th>
            <th colspan="3"> المبلغ </th>
           
        </tr>
        <tr>
         
        <th>كلي </th>
            <th>جزئي </th>
            <th>إجمالي </th>
            <th>بدل الحجز (كلي)</th>
            <th>بدل الحجز (جزئي)</th>
            <th>إجمالي بدل الحجز</th>
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
            url: '{{ route('reservation_report.main_department_employees_data', ['departmentId' => $department->uuid]) }}', // Use uuid
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
                    return `<a href="/reservation_report/user/${row.uuid}/details?start_date=${startDate}&end_date=${endDate}" style="color:#17a2b8 !important;">${data}</a>`;
                }
             },
            // { data: 'days', name: 'days',
            //     render: function(data, type, row) {
            //         const startDate = '{{ $startDate->format('Y-m-d') }}';
            //         const endDate = '{{ $endDate->format('Y-m-d') }}';
            //         return `<a href="/reservation_report/user/${row.uuid}/details?start_date=${startDate}&end_date=${endDate}" style="color:#17a2b8 !important;">${data}</a>`;
            //     }
            //  },
            // { 
            //     data: 'allowance', 
            //     name: 'allowance',
            //     render: function(data, type, row) {
            //         const startDate = '{{ $startDate->format('Y-m-d') }}';
            //         const endDate = '{{ $endDate->format('Y-m-d') }}';
            //         return `<a href="/reservation_report/user/${row.uuid}/details?start_date=${startDate}&end_date=${endDate}" style="color:#17a2b8 !important;">${data}</a>`;
            //     }
            // }


            { data: 'full_days', name: 'full_days',
                // render: function(data, type, row) {
                //     const startDate = '{{ $startDate->format('Y-m-d') }}';
                //     const endDate = '{{ $endDate->format('Y-m-d') }}';
                //     return `<a href="/reservation_report/user/${row.uuid}/details?start_date=${startDate}&end_date=${endDate}" style="color:#17a2b8 !important;">${data}</a>`;
                // }
             },
        { data: 'partial_days', name: 'partial_days',
            // render: function(data, type, row) {
            //         const startDate = '{{ $startDate->format('Y-m-d') }}';
            //         const endDate = '{{ $endDate->format('Y-m-d') }}';
            //         return `<a href="/reservation_report/user/${row.uuid}/details?start_date=${startDate}&end_date=${endDate}" style="color:#17a2b8 !important;">${data}</a>`;
            //     }
         },
        { data: 'total_days', name: 'total_days',
            render: function(data, type, row) {
                    const startDate = '{{ $startDate->format('Y-m-d') }}';
                    const endDate = '{{ $endDate->format('Y-m-d') }}';
                    return `<a href="/reservation_report/user/${row.uuid}/details?start_date=${startDate}&end_date=${endDate}" style="color:#17a2b8 !important;">${data}</a>`;
                }
         },
        { data: 'full_allowance', name: 'full_allowance',
            //  render: function(data, type, row) {
            //         const startDate = '{{ $startDate->format('Y-m-d') }}';
            //         const endDate = '{{ $endDate->format('Y-m-d') }}';
            //         return `<a href="/reservation_report/user/${row.uuid}/details?start_date=${startDate}&end_date=${endDate}" style="color:#17a2b8 !important;">${data}</a>`;
            //     }
            },
        { data: 'partial_allowance', name: 'partial_allowance',
            // render: function(data, type, row) {
            //         const startDate = '{{ $startDate->format('Y-m-d') }}';
            //         const endDate = '{{ $endDate->format('Y-m-d') }}';
            //         return `<a href="/reservation_report/user/${row.uuid}/details?start_date=${startDate}&end_date=${endDate}" style="color:#17a2b8 !important;">${data}</a>`;
            //     }
         },
        { data: 'total_allowance', name: 'total_allowance',
            render: function(data, type, row) {
                    const startDate = '{{ $startDate->format('Y-m-d') }}';
                    const endDate = '{{ $endDate->format('Y-m-d') }}';
                    return `<a href="/reservation_report/user/${row.uuid}/details?start_date=${startDate}&end_date=${endDate}" style="color:#17a2b8 !important;">${data}</a>`;
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
        const url = `{{ route('reservation_report.main_department_employees_print', ['departmentId' => $department->uuid]) }}?start_date=${startDate}&end_date=${endDate}`; // Use uuid
        window.open(url, '_blank');
    });
});
</script>
@endpush
