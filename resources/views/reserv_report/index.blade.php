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
@extends('layout.main') 

@push('style')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js" defer></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer></script>
@endpush

@section('title')
    تقارير بدل حجز
@endsection

@section('content')
    <div class="row"  style="direction: rtl">
        <div class="container welcome col-11">
            <div class="d-flex justify-content-between">
                <h3>تقارير بدل حجز</h3>
            </div>
        </div>
    </div>

    <div class="row" style="direction: rtl">
        <div class="container col-11 mt-3 p-0 pt-5 pb-4">
            <!-- Date Picker- Section -->
            <div class="d-flex justify-content-end">
                <label for="start-date" class="form-label mx-2">من تاريخ</label>
                <input type="date" id="start-date" name="start_date" class="form-control w-25 mx-2">
                <label for="end-date" class="form-label mx-2">إلى تاريخ</label>
                <input type="date" id="end-date" name="end_date" class="form-control w-25">
                <button id="fetch-report" class="btn btn-primary mx-2">عرض التقرير</button>
                <button id="print-report" class="btn btn-secondary mx-2">طباعة</button>
            </div>

    <!-- Info Boxes for Summary -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="info-box">
                <h5>عدد القطاعات المحجوزة</h5>
                <p id="total-sectors">0</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <h5>عدد الإدارات</h5>
                <p id="total-departments">0</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <h5>عدد الموظفين</h5>
                <p id="total-users">0</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <h5>الإجمالي</h5>
                <p id="total-amount">0</p>
            </div>
        </div>
    </div>

            <!-- Data Table for Detailed Report -->
            <div class="row">
    <div class="container col-11 mt-3">
        <div class="bg-white p-4">
                <table id="users-table" class="display table table-bordered table-hover dataTable">
                    <thead>
                    <tr>
                        <th>الترتيب</th>
                        <th>القطاع</th>
                        <th>الادارات الرئيسيه</th>
                        <th>الادارات الفرعيه</th>
                        <th>عدد الموظفين</th>
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
    // Initialize DataTable without initial data load
    var reportTable = $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        // deferLoading: 0, // Prevent initial data load
        ajax: {
            url: '{{ route('reservation_report.getReportData') }}',
            data: function(d) {
                d.start_date = $('#start-date').val();
                d.end_date = $('#end-date').val();
            }
        },
        bAutoWidth: false,
        columns: [
            { 
                data: null, 
                orderable: false, 
                searchable: false,
                render: function(data, type, row, meta) {
                    return meta.row + 1; // Display row number
                }
            },
            {
    data: 'sector_name',
    name: 'sector_name',
    render: function(data, type, row) {
        return `<a href="/reservation_report/sector/${row.sector_id}/details?start_date=${$('#start-date').val()}&end_date=${$('#end-date').val()}" style="color:blue !important;">${data}</a>`;
    }
},

       { data: 'main_departments_count',
        name: 'main_departments_count',
        render: function(data, type, row) {
            return `<a href="/reservation_report/sector/${row.sector_id}/departments?start_date=${$('#start-date').val()}&end_date=${$('#end-date').val()}" style="color:blue !important;">${data}</a>`;
        }},
            { data: 'sub_departments_count', name: 'sub_departments_count', searchable: true ,render: function(data, type, row) {
            return `<a href="/reservation_report/sector/${row.sector_id}/departments?start_date=${$('#start-date').val()}&end_date=${$('#end-date').val()}" style="color:blue !important;">${data}</a>`;
        }},
            { 
    data: 'employee_count', 
    name: 'employee_count',
    render: function(data, type, row) {
        return `<a href="/reservation_report/sector/${row.sector_id}/details?start_date=${$('#start-date').val()}&end_date=${$('#end-date').val()}" style="color:blue !important;">${data}</a>`;
    }
},
            { data: 'total_amount', name: 'total_amount', searchable: true,
                render: function(data, type, row) {
        return `<a href="/reservation_report/sector/${row.sector_id}/details?start_date=${$('#start-date').val()}&end_date=${$('#end-date').val()}" style="color:blue !important;">${data}</a>`;
    }
            }
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

    // Fetch report data and update summary on button click
    $('#fetch-report').click(function() {
        // Reload the DataTable to fetch new data
        reportTable.ajax.reload();
        
        // Update selected date range display
        const startDate = $('#start-date').val();
        const endDate = $('#end-date').val();
        $('#selected-dates').text('من ' + startDate + ' إلى ' + endDate);
    });

    // Update summary boxes on table data load
    reportTable.on('xhr.dt', function(e, settings, json) {
        if (json) {
            $('#total-sectors').text(json.totalSectors);
            $('#total-departments').text(json.totalDepartments);
            $('#total-users').text(json.totalUsers);
            $('#total-amount').text(json.totalAmount + ' د.ك');
        }
    });

    // Print report
    $('#print-report').click(function() {
        const startDate = $('#start-date').val();
        const endDate = $('#end-date').val();
        
        if (startDate && endDate) {
            const url = `{{ route('reservation_report.print') }}?start_date=${startDate}&end_date=${endDate}`;
            window.open(url, '_blank');
        } else {
            alert('يرجى اختيار نطاق تاريخ صحيح');
        }
    });
});

</script>

@endpush
