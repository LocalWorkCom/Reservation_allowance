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
            <p>تفاصيل بدل حجز لموظفين قطاع {{ $sectorName }}</p>
            
            <button id="print-report" class="btn btn-primary">طباعة</button>

        </div>
    </div>
</div>

<div class="row">
    <div class="container col-11 mt-3 p-0 pt-5 pb-4">
        <div class="col-lg-12">
            <div class="bg-white">
                @if (session()->has('message'))
                    <div class="alert alert-info">
                        {{ session('message') }}
                    </div>
                @endif
                <div>
                <table id="users-table" class="display table table-responsive-sm table-bordered table-hover dataTable">
                <thead>
                    <tr>
                        <th class="index-column">الترتيب</th>
                        <th class="file-number-column">رقم الملف</th> 
                        <th class="name-column">الاسم</th>
                        <th class="grade-column">الرتبه</th>
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
        { data: null, name: 'order', orderable: false, searchable: false },
        { data: 'file_number', name: 'file_number' }, 
        { data: 'name', name: 'name' },
        { data: 'grade', name: 'grade' },
        { data: 'department', name: 'department' }, 
        { data: 'days', name: 'days' },
        { data: 'allowance', name: 'allowance' },
    ],
    order: [[1, 'asc']],
    "oLanguage": {
        "sSearch": "",
        "sSearchPlaceholder": "بحث",
        "sInfo": 'اظهار صفحة _PAGE_ من _PAGES_',
        "sInfoEmpty": 'لا توجد بيانات متاحه',
        "sInfoFiltered": '(تم تصفية من _MAX_ اجمالى البيانات)',
        "sLengthMenu": 'اظهار _MENU_ عنصر لكل صفحة',
        "sZeroRecords": 'نأسف لا توجد نتيجة مطابقة',
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
            },
            createdRow: function(row, data, dataIndex) {
                $('td', row).eq(0).html(dataIndex + 1);
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