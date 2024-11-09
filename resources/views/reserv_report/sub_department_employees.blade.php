@extends('layout.main')

@section('title', "تفاصيل الموظفين في الإدارة الفرعية: {$subDepartment->name}")

@push('style')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
@endpush

@section('content')
    <div class="row" style="direction: rtl;">
        <div class="container welcome col-11">
            <p>تفاصيل الموظفين في الإدارة الفرعية: {{ $subDepartment->name }} الفترة من: {{ $startDate->format('Y-m-d') }} إلى: {{ $endDate->format('Y-m-d') }}</p>
        </div>
        <button id="print-report" class="btn btn-secondary">طباعة</button>

    </div>

    <div class="row" style="direction: rtl;">
        <div class="container col-11 mt-3 p-0 pt-5 pb-4">
            <table id="users-table" class="display table table-bordered table-hover dataTable">
                <thead>
                    <tr>
                        <th>الترتيب</th>
                        <th>اسم الموظف</th>
                        <th>مبلغ الحجز</th>
                        <th>تاريخ الحجز</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($employees as $index => $employee)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $employee['employee_name'] }}</td>
                            <td>{{ number_format($employee['reservation_amount'], 2) }} د.ك</td>
                            <td>{{ $employee['reservation_date'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
    $('#print-report').click(function() {
        const startDate = '{{ $startDate->format('Y-m-d') }}';
        const endDate = '{{ $endDate->format('Y-m-d') }}';
        const url = `{{ route('reservation_report.sub_department_employees_print', ['subDepartmentId' => $subDepartment->id]) }}?start_date=${startDate}&end_date=${endDate}`;
        window.open(url, '_blank');
    });
});
</script>
@endpush
