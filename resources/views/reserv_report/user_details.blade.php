@extends('layout.main')

@section('title', "تفاصيل بدل حجز للموظف {$user->name}")

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
<div class="row" style="direction: rtl">
    <div class="container welcome col-11">
        <div class="d-flex justify-content-between">
           
                <h4>تفاصيل بدل حجز للموظف <span class="text-info">{{ $user->name }}</span>  </h4>
                </div>
   </div>
  </div>
  
    <div class="container col-11 mt-3 py-4  " >
  
    <div class="d-flex flex-wrap justify-content-between mb-3"dir="rtl">
<h4>من : <span class="text-info">{{ $startDate->format('Y-m-d') }}</span> إلى : <span class="text-info">{{ $endDate->format('Y-m-d') }}</span></h4>
<button id="print-report" class="btn-blue ">طباعة</button>

</div>
        <table id="users-table" class="display table table-bordered table-hover dataTable">
            <thead>
                <tr>
                    <th>الترتيب</th>
                    <th>اليوم</th>
                    <th>التاريخ</th>
                    <th>النوع</th>
                    <th>المبلغ</th>
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
                url: '{{ route('reservation_report.user_details_data', ['userId' => $user->id]) }}',
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
                { data: 'day', name: 'day' },
                { data: 'date', name: 'date' },
                { data: 'type', name: 'type' },
                { data: 'amount', name: 'amount' }
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
        const startDate = '{{ $startDate->format('Y-m-d') }}';
        const endDate = '{{ $endDate->format('Y-m-d') }}';
        const url = `{{ route('reservation_report.user_details_print', ['userUuid' => $user->uuid]) }}?start_date=${startDate}&end_date=${endDate}`;
        window.open(url, '_blank');
    });
    });
</script>
@endpush
