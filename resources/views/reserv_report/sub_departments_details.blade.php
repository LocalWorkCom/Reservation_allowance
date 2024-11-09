@extends('layout.main')

@section('title', "تفاصيل الإدارات الفرعية للإدارة الرئيسية: {$mainDepartment->name}")

@push('style')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js" defer></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer></script>
    <style>
        .info-box {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
@endpush

@section('content')
    <div class="row" style="direction: rtl;">
        <div class="container welcome col-11">
            <div class="d-flex justify-content-between">
                <p>تفاصيل الإدارات الفرعية للإدارة الرئيسية: {{ $mainDepartment->name }}  الفترة من: {{ $startDate->format('Y-m-d') }} إلى: {{ $endDate->format('Y-m-d') }}</p>

            </div>
            <button id="print-report" class="btn btn-secondary">طباعة</button>

        </div>
    </div>

    <div class="row" style="direction: rtl;">
        <div class="container col-11 mt-3 p-0 pt-5 pb-4">

            <!-- Data Table -->
            <div class="mt-4 bg-white">
                <table id="users-table" class="display table table-bordered table-hover dataTable">
                    <thead>
                        <tr>
                            <th>الترتيب</th>
                            <th>اسم الإدارة الفرعية</th>
                            <th>عدد الموظفين</th>
                            <th>مبلغ الحجز</th> 
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($subDepartments as $index => $subDepartment)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $subDepartment['sub_department_name'] }}</td>
                                <td>
                                    <a href="{{ route('reservation_report.sub_department_employees', ['subDepartmentId' => $subDepartment['id']]) }}?start_date={{ $startDate->format('Y-m-d') }}&end_date={{ $endDate->format('Y-m-d') }}" style="color:blue !important;">
                                        {{ $subDepartment['employee_count'] }}
                                    </a>
                                </td>
                                <td>{{ $subDepartment['reservation_amount'] }}</td> 
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#users-table').DataTable({
        language: {
            sSearch: "",
            sSearchPlaceholder: "بحث",
            sInfo: 'اظهار صفحة _PAGE_ من _PAGES_',
            sInfoEmpty: 'لا توجد بيانات متاحه',
            sInfoFiltered: '(تم تصفية  من _MAX_ اجمالى البيانات)',
            sLengthMenu: 'اظهار _MENU_ عنصر لكل صفحة',
            sZeroRecords: 'نأسف لا توجد نتيجة',
            paginate: {
                sFirst: '<i class="fa fa-fast-backward" aria-hidden="true"></i>',
                sPrevious: '<i class="fa fa-chevron-left" aria-hidden="true"></i>',
                sNext: '<i class="fa fa-chevron-right" aria-hidden="true"></i>',
                sLast: '<i class="fa fa-step-forward" aria-hidden="true"></i>'
            }
        },
        pagingType: "full_numbers"
    });

    // Print button action
    $('#print-report').click(function() {
        const startDate = '{{ $startDate->format('Y-m-d') }}';
        const endDate = '{{ $endDate->format('Y-m-d') }}';
        const url = `{{ route('reservation_report.main_department_sub_departments_print', ['departmentId' => $mainDepartment->id]) }}?start_date=${startDate}&end_date=${endDate}`;
        window.open(url, '_blank');
    });
});
</script>
@endpush
