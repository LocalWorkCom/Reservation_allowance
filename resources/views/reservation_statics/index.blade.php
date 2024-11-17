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
                <h4>الاحصائيات بدل حجز - {{ $sector_name }}</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="container col-11 mt-3">
            <!-- DataTable -->
            <div class="bg-white p-4">
                <table id="users-table" class="display table table-bordered table-hover">
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
                    url: '{{ route('Reserv_statistic.getAll', ['sector_id' => $sector_id]) }}',
                    data: function (d) {
                        // Pass month and year as query parameters from the sector page
                        d.month = '{{ request()->query('month') }}';
                        d.year = '{{ request()->query('year') }}';
                    },
                },
                columns: [
                    { data: null, orderable: false, searchable: false }, // Auto-increment column
                    { 
                        data: 'department_name', 
                        render: function (data, type, row) {
                            return `<a href="{{ url('/statistics_subdepartments') }}/${row.id}?month={{ request()->query('month') }}&year={{ request()->query('year') }}" style="color: blue !important;">${data}</a>`;
                        },
                    },
                    { data: 'sub_departments_count', name: 'sub_departments_count',
                        render: function (data, type, row) {
                            return `<a href="{{ url('/statistics_subdepartments') }}/${row.id}?month={{ request()->query('month') }}&year={{ request()->query('year') }}" style="color: blue !important;">${data}</a>`;
                        },
                     },
                    { data: 'reservation_allowance_budget', name: 'reservation_allowance_budget' },
                    { data: 'registered_by', name: 'registered_by' },
                    { data: 'remaining_amount', name: 'remaining_amount' },
                    { data: 'number_of_employees', name: 'number_of_employees' },
                    { 
                    data: 'received_allowance_count', 
                    name: 'received_allowance_count', 
                    render: function(data, type, row) {
                        // Add query parameters for month and year
                        const month = '{{ request()->query('month') }}';
                        const year = '{{ request()->query('year') }}';
                        
                        // Link to a route that shows employees for this department
                        return `<a href="/department-employees/${row.id}?month=${month}&year=${year}" style="color:blue !important;">${data}</a>`;
                    },
},
                    { data: 'did_not_receive_allowance_count', name: 'did_not_receive_allowance_count' },
                ],
                order: [[1, 'asc']],
                language: {
                    sSearch: "",
                    sSearchPlaceholder: "بحث",
                    sInfo: 'اظهار صفحة _PAGE_ من _PAGES_',
                    sInfoEmpty: 'لا توجد بيانات متاحه',
                    sInfoFiltered: '(تم تصفية من _MAX_ اجمالى البيانات)',
                    sLengthMenu: 'اظهار _MENU_ عنصر لكل صفحة',
                    sZeroRecords: 'نأسف لا توجد نتيجة',
                    oPaginate: {
                        sFirst: '<i class="fa fa-fast-backward"></i>',
                        sPrevious: '<i class="fa fa-chevron-left"></i>',
                        sNext: '<i class="fa fa-chevron-right"></i>',
                        sLast: '<i class="fa fa-step-forward"></i>',
                    },
                },
                pagingType: "full_numbers",
                createdRow: function (row, data, dataIndex) {
                    $('td', row).eq(0).html(dataIndex + 1);
                },
            });
        });
    </script>
@endpush
