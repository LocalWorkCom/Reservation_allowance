<style>
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
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer></script>
@endpush


@section('title', '  تفاصيل الموظفين للإدارة')

@section('content')
    <div class="row">
    <div class="container welcome col-11">
    <div class="d-flex justify-content-between">
        <p>تفاصيل الموظفين للإدارة : <span class="text-info">{{ $departmentName }}</span></p>
        </div>
        </div>
    </div>

    <div class="row">
        <div class="container col-11 mt-3 p-0 pt-5 pb-4b ">
            <!-- DataTable -->
            <div class="bg-white p-4">
            <table id="users-table" class="display table table-responsive-sm table-bordered table-hover dataTable">
    <thead>
        
    <tr>
            <!-- <th colspan="5"></th> -->
            <th rowspan="2">الترتيب</th>
            <th rowspan="2">الرتبة</th>
            <th rowspan="2">الاسم</th>
            <th rowspan="2">رقم الملف</th>
          
            <th colspan="3"> ايام الحجز </th>
            <th colspan="3"> المبلغ </th>
           
        </tr>
        <tr>
         
            <th>كلي </th>
            <th>جزئي </th>
            <th>إجمالي </th>
            <th>بدل الحجز (كلي)</th>
            <th>بدل الحجز (جزئي)</th>
            <th>إجمالي بدل الحجز</th>
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
        $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("department.employees.getData", $departmentId) }}',
                data: {
                    month: '{{ $month }}',
                    year: '{{ $year }}',
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
            { data: 'name', name: 'name',
                render: function (data, type, row) {
                        const month = '{{ $month }}';
                        const year = '{{ $year }}';

                        return `<a href="/employee-allowance-details/${row.uuid}??month=${month}&year=${year}" style="color: #2f6289 !important;text-decoration: underline !important;">${data}</a>`;

                    }
             },
            { data: 'file_number', name: 'file_number',
                render: function (data, type, row) {
                        const month = '{{ $month }}';
                        const year = '{{ $year }}';

                        return `<a href="/employee-allowance-details/${row.uuid}??month=${month}&year=${year}" style="color: #2F6289 !important;

    text-decoration: underline !important;">${data}</a>`;
                    }
             },
            // { 
            //         data: 'allowance', 
            //         name: 'allowance',
                //     render: function (data, type, row) {
                //         const month = '{{ $month }}';
                //         const year = '{{ $year }}';
                //         return `<a href="/employee-allowance-details/${row.uuid}??month=${month}&year=${year}" style="    color: #2f6289 !important;
    // text-decoration: underline !important;">${data}</a>`;
                //     }
                // },
                
            // { 
            //         data: 'allowance', 
            //         name: 'allowance',
            //         render: function (data, type, row) {
            //             const month = '{{ $month }}';
            //             const year = '{{ $year }}';
            //             return `<a href="/employee-allowance-details/${row.uuid}?month=${month}&year=${year}" style="    color: #2f6289 !important;
    // text-decoration: underline !important;">${data}</a>`;
            //         }
            //     },

            { data: 'full_days', name: 'full_days'  ,
                    //   render: function (data, type, row) {
                    //     const month = '{{ $month }}';
                    //     const year = '{{ $year }}';
                    //     return `<a href="/employee-allowance-details/${row.uuid}??month=${month}&year=${year}" style="    color: #2f6289 !important;
    // text-decoration: underline !important;">${data}</a>`;
                    // }
                },
        { data: 'partial_days', name: 'partial_days',
                    //   render: function (data, type, row) {
                    //     const month = '{{ $month }}';
                    //     const year = '{{ $year }}';
                    //     return `<a href="/employee-allowance-details/${row.uuid}??month=${month}&year=${year}" style="    color: #2f6289 !important;
    // text-decoration: underline !important;">${data}</a>`;
                    // } 
                },
        { data: 'total_days', name: 'total_days',
                      render: function (data, type, row) {
                        const month = '{{ $month }}';
                        const year = '{{ $year }}';

                        return `<a href="/employee-allowance-details/${row.uuid}??month=${month}&year=${year}" style="color: #2F6289 !important;

    text-decoration: underline !important;">${data}</a>`;
                    } 
                },
        { data: 'full_allowance', name: 'full_allowance' ,
                    //   render: function (data, type, row) {
                    //     const month = '{{ $month }}';
                    //     const year = '{{ $year }}';
                    //     return `<a href="/employee-allowance-details/${row.uuid}??month=${month}&year=${year}" style="    color: #2f6289 !important;
    // text-decoration: underline !important;">${data}</a>`;
                    // }
                },
        { data: 'partial_allowance', name: 'partial_allowance' ,
                    //   render: function (data, type, row) {
                    //     const month = '{{ $month }}';
                    //     const year = '{{ $year }}';
                    //     return `<a href="/employee-allowance-details/${row.uuid}??month=${month}&year=${year}" style="    color: #2f6289 !important;
    // text-decoration: underline !important;">${data}</a>`;
                    // }
                },
        { data: 'total_allowance', name: 'total_allowance' ,
                      render: function (data, type, row) {
                        const month = '{{ $month }}';
                        const year = '{{ $year }}';

                        return `<a href="/employee-allowance-details/${row.uuid}??month=${month}&year=${year}" style="color: #2F6289 !important;

    text-decoration: underline !important;">${data}</a>`;
                    }},
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
</script>
@endpush
