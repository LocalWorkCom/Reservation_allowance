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

@section('title', "تفاصيل الإدارات الفرعية للإدارة: $departmentName")

@section('content')
<div class="row">
    <div class="container welcome col-11">
        <div class="d-flex justify-content-between align-items-center">
            <h4>الإدارات الفرعية للإدارة: {{ $departmentName }}</h4>
            <div>
                <p><strong>الشهر:</strong> {{ $month }}</p>
                <p><strong>السنة:</strong> {{ $year }}</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="container col-11 mt-3">
        <div class="bg-white p-4">
            <table id="users-table" class="display table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم الإدارة الفرعية</th>
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
    $(document).ready(function() {
        $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("statistics_subdepartments.getAll", $departmentId) }}',
                data: function(d) {
                    d.month = '{{ $month }}';
                    d.year = '{{ $year }}';
                }
            },
            columns: [
                { data: null, orderable: false, searchable: false }, 
                {
                    data: 'sub_department_name', 
                    name: 'sub_department_name',
                },
                { data: 'reservation_allowance_budget', name: 'reservation_allowance_budget' },
                { data: 'registered_by', name: 'registered_by' },
                { data: 'remaining_amount', name: 'remaining_amount' },
                { data: 'employees_count', name: 'employees_count' },
                { 
                    data: 'received_allowance_count', 
                    name: 'received_allowance_count',
                    render: function(data, type, row) {
                        const month = '{{ $month }}';
                        const year = '{{ $year }}';
                        return `<a href="/department-employees/${row.id}?month=${month}&year=${year}" style="color: blue !important;">${data}</a>`;
                    }
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
            createdRow: function(row, data, dataIndex) {
                $('td', row).eq(0).html(dataIndex + 1); 
            },
        });
    });
</script>
@endpush
