@extends('layout.main')

@section('title', "تفاصيل الإدارات الرئيسية للقطاع: {$sector->name}")

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
                <p>تفاصيل بدل حجز الإدارات الرئيسية قطاع {{ $sector->name }} الفترة من: {{ $startDate->format('Y-m-d') }} إلى: {{ $endDate->format('Y-m-d') }}</p>

            </div>
            <button id="print-report" class="btn btn-secondary">طباعة</button>

        </div>
    </div>

    <div class="row" style="direction: rtl;">
        <div class="container col-11 mt-3 p-0 pt-5 pb-4">
            <!-- Date Range -->

                <!-- Data Table -->
                <div class="mt-4 bg-white">
                <table id="users-table" class="display table table-bordered table-hover dataTable">
                    <thead>
                        <tr>
                            <th>الترتيب</th>
                            <th>اسم الإدارة الرئيسية</th>
                            <th>عدد الإدارات الفرعية</th>
                            <th>عدد الموظفين</th>
                            <th>مبلغ الحجز</th> 
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mainDepartments as $index => $department)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $department['department_name'] }}</td>
                                <td>
                                    @if(isset($department['id']))
                                        <a href="{{ route('reservation_report.main_department_sub_departments', ['departmentId' => $department['id']]) }}?start_date={{ $startDate->format('Y-m-d') }}&end_date={{ $endDate->format('Y-m-d') }}" style="color:blue !important;">
                                            {{ $department['sub_departments_count'] }}
                                        </a>
                                    @else
                                        {{ $department['sub_departments_count'] }}
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('reservation_report.main_department_employees', ['departmentId' => $department['id']]) }}?start_date={{ $startDate->format('Y-m-d') }}&end_date={{ $endDate->format('Y-m-d') }}" style="color:blue !important;">
                                        {{ $department['employee_count'] }}
                                    </a>
                                </td>
                                <td>
                                <a href="{{ route('reservation_report.main_department_employees', ['departmentId' => $department['id']]) }}?start_date={{ $startDate->format('Y-m-d') }}&end_date={{ $endDate->format('Y-m-d') }}" style="color:blue !important;">

                                    {{ $department['reservation_amount'] }}
                                
                                </td> 
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
        const url = `{{ route('reservation_report.sector_main_departments_print', ['sectorId' => $sector->id]) }}?start_date=${startDate}&end_date=${endDate}`;
        window.open(url, '_blank');
    });
});
</script>
@endpush
