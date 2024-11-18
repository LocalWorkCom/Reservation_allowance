@extends('layout.main')

@section('title', "تفاصيل الموظفين في الإدارة الفرعية: {$subDepartment->name}")

@push('style')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js" defer></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer></script>

    @endpush

@section('content')
    <div class="row" style="direction: rtl;"> 
        <div class="container welcome col-11">
        <div class="d-flex justify-content-between">
            <p>تفاصيل الموظفين في الإدارة الفرعية: {{ $subDepartment->name }} الفترة من: {{ $startDate->format('Y-m-d') }} إلى: {{ $endDate->format('Y-m-d') }}</p>
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
                        <th>اليوم</th>
                        <th>التاريخ</th>
                        <th>اسم الموظف</th>
                        <th>رقم الملف</th>
                        <th>الرتبة</th>
                        <th>نوع الحجز</th>
                        <th>المبلغ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($employees as $index => $employee)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $employee['day'] }}</td>
                            <td>{{ $employee['date'] }}</td>
                            <td>{{ $employee['employee_name'] }}</td>
                            <td>{{ $employee['file_number'] }}</td>
                            <td>{{ $employee['grade'] }}</td>
                            <td>{{ $employee['type'] }}</td>
                            <td>{{ $employee['reservation_amount'] }} د.ك</td>
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
                sFirst: '<i class="fa fa-fast-backward"></i>',
                sPrevious: '<i class="fa fa-chevron-left"></i>',
                sNext: '<i class="fa fa-chevron-right"></i>',
                sLast: '<i class="fa fa-step-forward"></i>'
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
