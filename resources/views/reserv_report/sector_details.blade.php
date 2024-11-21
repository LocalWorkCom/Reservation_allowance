@extends('layout.main')

@section('title', "تفاصيل بدل حجز لموظفي قطاع {$sector->name}")

@push('style')
<link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
    <script type="text/javascript" charset="utf8"
        src="https://code.jquery.com/jquery-3.5.1.js" defer></script>
    <script type="text/javascript" charset="utf8"
        src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer>
    </script>
    @endpush
@section('content')
<div class="row" >
    <div class="container welcome col-11">
        <div class="d-flex justify-content-between">
            <div class="mt-4 bg-white">
                <p>تفاصيل بدل حجز لموظفي قطاع {{ $sector->name }} الفترة من: {{ $startDate->format('Y-m-d') }} إلى: {{ $endDate->format('Y-m-d') }}</p>
            </div>
        </div>
        <br><br>
        <button id="print-report" class="btn btn-secondary mx-2">طباعة</button>
        <table id="users-table" class="display table table-bordered table-hover dataTable">
            <thead>
                <tr>
                    <th>الترتيب</th>
                    <th>الرتبة</th>
                    <th>الاسم</th>
                    <th>رقم الملف</th>
                    
                    <th>الإدارة</th>
                    <th>عدد الأيام</th>
                    <th>المبلغ الإجمالي</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $.fn.dataTable.ext.classes.sPageButton =
        'btn-pagination btn-sm';
        $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('reservation_report.sector_details_data', ['sectorId' => $sector->id]) }}',
                data: {
                    start_date: '{{ $startDate->format('Y-m-d') }}',
                    end_date: '{{ $endDate->format('Y-m-d') }}'
                }
            },
            columns: [
                { 
                    data: null, 
                    orderable: false, 
                    searchable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                { data: 'grade', name: 'grade' },
                { data: 'name', name: 'name', render: function(data, type, row) {
                        return `<a href="/reservation_report/user/${row.id}/details?start_date={{ $startDate->format('Y-m-d') }}&end_date={{ $endDate->format('Y-m-d') }}" style="color:blue !important;">${data}</a>`;
                    } },
                { data: 'file_number', name: 'file_number', render: function(data, type, row) {
                        return `<a href="/reservation_report/user/${row.id}/details?start_date={{ $startDate->format('Y-m-d') }}&end_date={{ $endDate->format('Y-m-d') }}" style="color:blue !important;">${data}</a>`;
                    } },
                { data: 'department', name: 'department' },
                { data: 'days', name: 'days',
                    render: function(data, type, row) {
                        return `<a href="/reservation_report/user/${row.id}/details?start_date={{ $startDate->format('Y-m-d') }}&end_date={{ $endDate->format('Y-m-d') }}" style="color:blue !important;">${data}</a>`;
                    }
                 },
                { 
                    data: 'allowance', 
                    name: 'allowance',
                    render: function(data, type, row) {
                        return `<a href="/reservation_report/user/${row.id}/details?start_date={{ $startDate->format('Y-m-d') }}&end_date={{ $endDate->format('Y-m-d') }}" style="color:blue !important;">${data}</a>`;
                    }
                }
            ],
            order: [[2, 'desc']],
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
                if (pageInfo.recordsTotal <= 10) {
                    $('.dataTables_paginate').css('visibility', 'hidden');
                } else {
                    $('.dataTables_paginate').css('visibility', 'visible');
                }
            }
        });

        $('#print-report').click(function() {
            const url = `{{ route('reservation_report.sector_details_print', ['sectorId' => $sector->id]) }}?start_date={{ $startDate->format('Y-m-d') }}&end_date={{ $endDate->format('Y-m-d') }}`;
            window.open(url, '_blank');
        });
    });
</script>

@endpush
