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
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer></script>
@endpush

@section('title', "تفاصيل بدل حجز لموظفي إدارة {$department->name}")

@section('content')
<div class="container">
    <h3>تفاصيل بدل حجز لموظفي إدارة {{ $department->name }}</h3>
    <p>الفترة من: {{ $startDate->format('Y-m-d') }} إلى: {{ $endDate->format('Y-m-d') }}</p>

    <table id="employee-details-table" class="display table table-bordered table-hover dataTable">
        <thead>
            <tr>
                <th>الترتيب</th>
                <th>اليوم</th>
                <th>التاريخ</th>
                <th>الاسم</th>
                <th>الإدارة</th>
                <th>النوع</th>
                <th>المبلغ</th>
            </tr>
        </thead>
    </table>
</div>
@endsection

@push('scripts')
<script>
   $(document).ready(function() {
    $('#employee-details-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('reservation_report.department_details_data', ['departmentId' => $department->id]) }}',
            data: function(d) {
                const startDate = '{{ $startDate->format('Y-m-d') }}';
                const endDate = '{{ $endDate->format('Y-m-d') }}';
                console.log("Start Date:", startDate); 
                console.log("End Date:", endDate);     
                d.start_date = startDate;
                d.end_date = endDate;
            },

        },
        columns: [
            { data: null, name: 'order', orderable: false, searchable: false },
            { data: 'day', name: 'day' },
            { data: 'date', name: 'date' },
            { data: 'name', name: 'name' },
            { data: 'department', name: 'department' },
            { data: 'type', name: 'type' },
            { data: 'amount', name: 'amount' }
        ],
        order: [[2, 'asc']],
        language: {
            search: "بحث:",
            paginate: {
                first: "الأول",
                last: "الأخير",
                next: "التالي",
                previous: "السابق"
            },
            info: "إظهار _PAGE_ من _PAGES_",
            infoEmpty: "لا توجد بيانات",
            zeroRecords: "لم يتم العثور على نتائج"
        },
        createdRow: function(row, data, index) {
            $('td', row).eq(0).html(index + 1); // Add serial number to "order" column
        }
    });
});

</script>
@endpush
