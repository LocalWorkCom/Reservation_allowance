<style>
    /* Updated Styles */
    .info-box {
        background-color: white;
        padding: 20px;
        border-radius: 10px;
        margin-top: 20px;
        text-align: center;
        
    }

    .index-column { width: 5% !important; }
    .name-column { width: 10% !important; }
    .file-number-column { width: 10% !important; }
    .grade-column { width: 10% !important; }
    .days-column { width: 35% !important; }
    .department-column { width: 10% !important; }
    .allowance-column { width: 40% !important; }
</style>

@extends('layout.main')

@push('style')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js" defer></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer></script>
@endpush

@section('title')
    قطاع
@endsection

@section('content')
<div class="row">
    <div class="container welcome col-11">
        <div class="d-flex justify-content-between">
            <p>   موظفين قطاع <span class="text-info"> {{ $sectorName }}</span></p>
            <button id="print-report" class="btn-blue">طباعة</button>

        </div>
    </div>
</div>

<div class="row">
    <div class="container col-11 mt-3 py-5 pb-4">
       
            <div class="bg-white">
                @if (session()->has('message'))
                    <div class="alert alert-info">
                        {{ session('message') }}
                    </div>
                @endif
           
                <table id="users-table" class="display table table-responsive-sm table-bordered table-hover dataTable">
                <thead>
                    <tr>
                        <th class="index-column">الترتيب</th>
                        <th class="grade-column">الرتبه</th>
                        <th class="name-column">الاسم</th>
                        <th class="file-number-column">رقم الملف</th> 
                        <th class="department-column">الادارة</th>
                        <th class="days-column">الايام</th>
                        <th class="allowance-column">بدل الحجز</th>
                    </tr>
                </thead>
            </table>

                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#users-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: '{{ route('sectorEmployees.getData', ['sectorId' => $sectorId]) }}',
        data: function(d) {
            d.month = '{{ $month }}';
            d.year = '{{ $year }}';
        }
    },
    columns: [
    {
        data: null,
        orderable: false,
        searchable: false,
        render: function (data, type, row, meta) {
            return meta.row + 1; // Auto-generate row numbers
        }
    },
    { data: 'grade', name: 'grade' },
    {
        data: 'name',
        name: 'name',
        render: function (data, type, row) {
            const month = '{{ $month }}';
            const year = '{{ $year }}';
            return `<a href="/employee-allowance-details/${row.uuid}?month=${month}&year=${year}" style="color:#2f6289 !important;">${data}</a>`;
        }
    },
    {
        data: 'file_number',
        name: 'file_number',
        render: function (data, type, row) {
            const month = '{{ $month }}';
            const year = '{{ $year }}';
            return `<a href="/employee-allowance-details/${row.uuid}?month=${month}&year=${year}" style="color:#2f6289 !important;">${data}</a>`;
        }
    },
    { data: 'department', name: 'department' },
    {
        data: 'days',
        name: 'days',
        render: function (data, type, row) {
            const month = '{{ $month }}';
            const year = '{{ $year }}';
            return `<a href="/employee-allowance-details/${row.uuid}?month=${month}&year=${year}" style="color:#2f6289 !important;">${data}</a>`;
        }
    },
    {
        data: 'allowance',
        name: 'allowance',
        render: function (data, type, row) {
            const month = '{{ $month }}';
            const year = '{{ $year }}';
            return `<a href="/employee-allowance-details/${row.uuid}?month=${month}&year=${year}" style="color:#2f6289 !important;">${data}</a>`;
        }
    },
],

            order: [[1, 'asc']],
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
    });

    $('#print-report').click(function() {
    const month = '{{ $month }}';
    const year = '{{ $year }}';
    const url = `{{ route('sectorEmployees.printReport', ['sectorId' => $sectorId]) }}?month=${month}&year=${year}`;
    window.open(url, '_blank');
});





</script>
@endpush
@endsection
