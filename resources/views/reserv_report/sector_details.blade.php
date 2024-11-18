@extends('layout.main')

@section('title', "تفاصيل بدل حجز لموظفي قطاع {$sector->name}")

@push('style')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js" defer></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer></script>
    
    <style>
        .info-box {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            text-align: center;
        }
    </style>
@endpush

@section('content')

<div class="row" style="direction: rtl">
    <div class="container welcome col-11">
        <div class="d-flex justify-content-between">
            <div class="mt-4 bg-white" style="direction: rtl">
                <p>تفاصيل بدل حجز لموظفي قطاع {{ $sector->name }} الفترة من: {{ $startDate->format('Y-m-d') }} إلى: {{ $endDate->format('Y-m-d') }}</p>
            </div>
        </div>
        <br><br>
        <button id="print-report" class="btn btn-secondary mx-2">طباعة</button>
        <table id="users-table" class="display table table-bordered table-hover dataTable">
    <thead>
        <tr>
            <th>الترتيب</th>
            <th>اليوم</th>
            <th>التاريخ</th>
            <th>الاسم</th>
            <th>رقم الملف</th>
            <th>الرتبة</th> <!-- New column for grade -->
            <th>الإدارة</th>
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
        { data: 'day', name: 'day' },
        { data: 'date', name: 'date' },
        { data: 'name', name: 'name' },
        { data: 'file_number', name: 'file_number' },
        { data: 'grade', name: 'grade' }, 
        { data: 'department', name: 'department' },
        { data: 'type', name: 'type' },
        { data: 'amount', name: 'amount' }
    ],
    order: [[5, 'asc']], 
    language: {
        sSearch: "",
        sSearchPlaceholder: "بحث",
        sInfo: 'اظهار صفحة _PAGE_ من _PAGES_',
        sInfoEmpty: 'لا توجد بيانات متاحه',
        sInfoFiltered: '(تم تصفية من _MAX_ اجمالى البيانات)',
        sLengthMenu: 'اظهار _MENU_ عنصر لكل صفحة',
        sZeroRecords: 'نأسف لا توجد نتيجة',
        paginate: {
            first: "الأول",
            last: "الأخير",
            next: "التالي",
            previous: "السابق"
        }
    },
    pagingType: "full_numbers"
});


        $('#print-report').click(function() {
            const url = `{{ route('reservation_report.sector_details_print', ['sectorId' => $sector->id]) }}?start_date={{ $startDate->format('Y-m-d') }}&end_date={{ $endDate->format('Y-m-d') }}`;
            window.open(url, '_blank');
        });
    });
</script>
@endpush
