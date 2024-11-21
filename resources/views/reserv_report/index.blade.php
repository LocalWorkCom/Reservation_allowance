@extends('layout.main')

@push('style')
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
    <script type="text/javascript" charset="utf8"
        src="https://code.jquery.com/jquery-3.5.1.js" defer></script>
    <script type="text/javascript" charset="utf8"
        src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer>
    </script>
@endpush

@section('title', 'تقارير بدل حجز')

@section('content')
<div class="row">
    <div class="container welcome col-11">
        <div class="d-flex justify-content-between">
            <p>تقارير بدل حجز</p>
        </div>
    </div>
    <button id="print-report" class="btn btn-secondary mx-2">طباعة</button>

</div>

<div class="row">
    <div class="container col-11 mt-3 p-0 pt-5 pb-4">
        <!-- Form Section -->
        <form id="filter-form" class="d-flex align-items-center mb-4">
            <label for="start-date" class="me-2">من تاريخ:</label>
            <input type="date" id="start-date" name="start_date" class="form-control me-3" required>
            <label for="end-date" class="me-2">إلى تاريخ:</label>
            <input type="date" id="end-date" name="end_date" class="form-control me-3" required>
            <button type="submit" class="btn btn-primary">عرض التقرير</button>
        </form>

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
                    <p id="total-amount">0 د.ك</p>
                </div>
            </div>
        </div>

        <!-- Data Table for Detailed Report -->
        <div class="mt-4 bg-white">
            <table id="users-table" class="display table table-responsive-sm table-bordered table-hover dataTable">
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
        $.fn.dataTable.ext.classes.sPageButton =
        'btn-pagination btn-sm';
        var table = $('#users-table').DataTable({
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
        ],  order: [
                    [1, 'desc']
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
                    // Check if the total number of records is less than or equal to the number of entries per page
                    if (pageInfo.recordsTotal <= 10) { // Adjust this number based on your page length
                        $('.dataTables_paginate').css('visibility', 'hidden'); // Hide pagination
                    } else {
                        $('.dataTables_paginate').css('visibility', 'visible'); // Show pagination
                    }
                }
            });
        

        $('#filter-form').on('submit', function(e) {
            e.preventDefault();
            table.ajax.reload();

            $.ajax({
                url: '{{ route('reservation_report.getReportData') }}',
                type: 'GET',
                data: {
                    start_date: $('#start-date').val(),
                    end_date: $('#end-date').val()
                },
                success: function(response) {
                    if (response) {
                        $('#total-sectors').text(response.totalSectors || 0);
                        $('#total-departments').text(response.totalDepartments || 0);
                        $('#total-users').text(response.totalUsers || 0);
                        $('#total-amount').text(response.totalAmount || '0 د.ك');
                    }
                },
                error: function() {
                    $('#total-sectors, #total-departments, #total-users, #total-amount').text('0');
                }
            });
        });
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
  
</script>
@endpush
