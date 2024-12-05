@extends('layout.main')

@section('title', "تفاصيل الموظفين في الإدارة الفرعية: {$subDepartment->name}")

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
<div class="row" >
        <div class="container welcome col-11">
            <div class="d-flex justify-content-between">
            <p>تفاصيل الموظفين في الإدارة الفرعية: {{ $subDepartment->name }}   </p>
            </div>
           

           </div>
       </div>
   

<div class="container col-11 mt-3 py-5  " >
<div class="d-flex justify-content-between pb-3"dir="rtl">
        <h4> الفترة من: <span class="text-info">{{ $startDate->format('Y-m-d') }}</span> إلى: <span class="text-info">{{ $endDate->format('Y-m-d') }}</span></h4>

        <button id="print-report" class="btn btn-secondary">طباعة</button>

        </div>
        <div class="mt-4 bg-white">
            <table id="users-table" class="display table table-bordered table-hover dataTable">
            <thead>
    <tr>
        <th>الترتيب</th>
        <th>اليوم</th>
        <th>التاريخ</th>
        <th>الرتبة</th>
        <th>اسم الموظف</th>
        <th>رقم الملف</th>
        <th>نوع الحجز</th>
        <th>المبلغ</th>
        <th>اضيف بواسطة</th>
        <th>توقيت الإضافة</th>
    </tr>
</thead>
<tbody>
    @foreach ($employees as $index => $employee)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $employee['day'] }}</td>
            <td>{{ $employee['date'] }}</td>
            <td>{{ $employee['grade'] }}</td>
            <td>{{ $employee['employee_name'] }}</td>
            <td>{{ $employee['file_number'] }}</td>
            <td>{{ $employee['type'] }}</td>
            <td>{{ $employee['reservation_amount'] }} د.ك</td>
            <td>{{ $employee['created_by'] }}</td>
            <td>{{ $employee['created_at'] }}</td>
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
    $.fn.dataTable.ext.classes.sPageButton =
    'btn-pagination btn-sm';
    $('#users-table').DataTable({
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

    $('#print-report').click(function() {
    const startDate = '{{ $startDate->format('Y-m-d') }}';
    const endDate = '{{ $endDate->format('Y-m-d') }}';
    const url = `{{ route('reservation_report.sub_department_employees_print', ['subDepartmentId' => $subDepartment->uuid]) }}?start_date=${startDate}&end_date=${endDate}`;
    window.open(url, '_blank');
});


});
</script>
@endpush
