@extends('layout.main')

@push('style')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js" defer></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer></script>
@endpush

@section('title', "تفاصيل بدل الحجز للموظف")

@section('content')
<div class="row">
    <div class="container welcome col-11">
        <div class="d-flex justify-content-between">
            <p>تفاصيل بدل الحجز للموظف: <span class="text-info">{{ $employeeName }}</span></p>
        </div>
    </div>
</div>

<div class="row">
    <div class="container col-11 mt-3 p-0 pt-5 pb-4">
        <div class="bg-white p-4">
        <table id="users-table" class="display table table-responsive-sm table-bordered table-hover dataTable">
                <thead>
                    <tr>
                        <th>الترتيب</th>
                        <th>التاريخ</th>
                        <th>النوع</th>
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
    $('#users-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: '{{ route('employee.allowance.details.data', $employeeUuid) }}', // Use UUID
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
                return meta.row + 1; 
            }
        },
        { data: 'date', name: 'date' },
        { data: 'type', name: 'type' },
        { data: 'amount', name: 'amount' },
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
});
</script>
@endpush
