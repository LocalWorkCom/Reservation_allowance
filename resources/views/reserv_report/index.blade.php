<style>
    /* Updated Styles */
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
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer>
    </script>
@endpush

@section('title')
    تقارير بدل حجز
@endsection

@section('content')
    <div class="row">
        <div class="container welcome col-11">
            <div class="d-flex justify-content-between">
                <h3>تقارير بدل حجز</h3>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="container col-11 mt-3">
            <!-- Date Range Picker Section -->
            <div class="d-flex justify-content-end">
                <label for="start-date" class="form-label mx-2">من</label>
                <input type="date" id="start-date" name="start_date" class="form-control w-25">
                <label for="end-date" class="form-label mx-2">إلى</label>
                <input type="date" id="end-date" name="end_date" class="form-control w-25">
                <button id="fetch-report" class="btn btn-primary mx-2">عرض التقرير</button>
            </div>

            <!-- Info Boxes for Summary -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="info-box">
                        <h5>عدد الإدارات</h5>
                        <p id="total-departments">0</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box">
                        <h5>عدد الموظفين الحاصلين على بدل حجز</h5>
                        <p id="total-users">0</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box">
                        <h5>اجمالى المسجل </h5>
                        <p id="total-amount">0 د.ك</p>
                    </div>
                </div>
            </div>

            <!-- Data Table for Detailed Report -->
            <div class="mt-4 bg-white">
                <table id="report-table" class="display table table-bordered table-hover dataTable">
                    <thead>
                        <tr>
                            <th>الترتيب</th>
                            <th>الادارات</th>
                            <th>عدد الحاصلين على بدل حجز</th>
                            <th>المبلغ</th>
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
    var reportTable = $('#report-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('reservation_report.getReportData') }}',
            data: function(d) {
                d.start_date = $('#start-date').val();
                d.end_date = $('#end-date').val();
            }
        },
        columns: [
            { 
                data: null, 
                orderable: false, 
                searchable: false,
                render: function(data, type, row, meta) {
                    return meta.row + 1;  // Auto-incrementing row number
                }
            },
            { data: 'department_name', name: 'department_name' },
            { data: 'user_count', name: 'user_count', searchable: false },  // Disable searching
            { data: 'total_amount', name: 'total_amount', searchable: false }  // Disable searching
        ],
        order: [[1, 'asc']],
        "oLanguage": {
            "sSearch": "",
            "sSearchPlaceholder": "بحث",
            "sInfo": 'اظهار صفحة _PAGE_ من _PAGES_',
            "sInfoEmpty": 'لا توجد بيانات متاحه',
            "sInfoFiltered": '(تم تصفية من _MAX_ اجمالى البيانات)',
            "sLengthMenu": 'اظهار _MENU_ عنصر لكل صفحة',
            "sZeroRecords": 'نأسف لا توجد نتيجة',
            "oPaginate": {
                "sFirst": '<i class="fa fa-fast-backward" aria-hidden="true"></i>',
                "sPrevious": '<i class="fa fa-chevron-left" aria-hidden="true"></i>',
                "sNext": '<i class="fa fa-chevron-right" aria-hidden="true"></i>',
                "sLast": '<i class="fa fa-step-forward" aria-hidden="true"></i>'
            }
        },
        pagingType: "full_numbers",
        fnDrawCallback: function(oSettings) {
            var page = this.api().page.info().pages;
            if (page == 1) {
                $('.dataTables_paginate').css('visibility', 'hidden');
            }
        }
    });

    // Fetch report data on button click
    $('#fetch-report').click(function() {
        reportTable.ajax.reload();
    });

    // Listen for draw event to update summary boxes
    reportTable.on('xhr.dt', function(e, settings, json) {
        if (json) {
            $('#total-departments').text(json.totalDepartments);
            $('#total-users').text(json.totalUsers);
            $('#total-amount').text(json.totalAmount + ' د.ك');
        }
    });
});

    </script>
@endpush
