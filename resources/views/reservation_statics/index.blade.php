<style>
    .info-box {
        background-color: white;
        padding: 20px;
        border-radius: 10px;
        margin-top: 20px;
        text-align: center;
    }
</style>

@extends('layout.main')

@push('style')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
<script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js" defer></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer></script>
@endpush

@section('title')
    الاحصائيات بدل حجز - {{ $sector_name }}
@endsection

@section('content')
<div class="row">
    <div class="container welcome col-11">
            <div class="d-flex justify-content-between">
                <p>الاحصائيات بدل حجز - {{ $sector_name }}</p>
            </div>
    </div>
</div>

<div class="row">
        <div class="container col-11 mt-3 p-0 pt-5 pb-4b ">
            <!-- DataTable -->
        <div class="bg-white p-4">
        <table id="users-table" class="display table table-responsive-sm table-bordered table-hover dataTable">
                <thead>
                    <tr>
                        <th>الترتيب</th>
                            <th>اسم الادارة</th>
                            <th>عدد الادارات الفرعية</th>
                            <th>ميزانية بدل الحجز</th>
                            <th>المسجل</th>
                            <th>المبلغ المتبقي</th>
                            <th>عدد الموظفين</th>
                            <th>الحاصلين على بدل حجز</th>
                            <th>لم يحصل على بدل حجز</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    const table = $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("Reserv_statistic.getAll", ["sector_id" => $sector_id]) }}',
            data: function (d) {
                d.month = '{{ request()->query("month") }}';
                d.year = '{{ request()->query("year") }}';
            },
        },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row, meta) {
                    return meta.row + 1; // Auto-generate row numbers
                }
            },
            {
                data: 'department_name',
                render: function (data, type, row) {
                    return `<a href="{{ url('/statistics_subdepartments') }}/${row.uuid}?month={{ request()->query('month') }}&year={{ request()->query('year') }}" style="color: blue !important;">${data}</a>`;
                },
            },
            {
                data: 'sub_departments_count',
                render: function (data, type, row) {
                    return `<a href="{{ url('/statistics_subdepartments') }}/${row.uuid}?month={{ request()->query('month') }}&year={{ request()->query('year') }}" style="color: blue !important;">${data}</a>`;
                }
            },
            { data: 'reservation_allowance_budget', name: 'reservation_allowance_budget' },
            { data: 'registered_by', name: 'registered_by',
                render: function (data, type, row) {
                    const month = '{{ request()->query("month") }}';
                    const year = '{{ request()->query("year") }}';
                    return `<a href="/department-employees/${row.uuid}?month=${month}&year=${year}" style="color:blue !important;">${data}</a>`;
                },
             },
            { data: 'remaining_amount', name: 'remaining_amount' },
            { data: 'number_of_employees', name: 'number_of_employees',
                render: function(data, type, row) {
                const month = '{{ request()->query("month") }}';
                const year = '{{ request()->query("year") }}';
                return `<a href="/all-department-employees/${row.uuid}?month=${month}&year=${year}" style="color:blue !important;">${data}</a>`;
            }
             },
            {
                data: 'received_allowance_count',
                render: function (data, type, row) {
                    const month = '{{ request()->query("month") }}';
                    const year = '{{ request()->query("year") }}';
                    return `<a href="/department-employees/${row.uuid}?month=${month}&year=${year}" style="color:blue !important;">${data}</a>`;
                },
            },
            { data: 'did_not_receive_allowance_count', name: 'did_not_receive_allowance_count',
                render: function(data, type, row) {
                const month = '{{ request()->query("month") }}';
                const year = '{{ request()->query("year") }}';
                return `<a href="/department-not-received/${row.uuid}?month=${month}&year=${year}" style="color:blue !important;">${data}</a>`;
            }
             },
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
});

    
</script>
@endpush
