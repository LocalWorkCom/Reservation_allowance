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
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer>
    </script>
@endpush

@section('title')
    تفاصيل المحجوزين - {{ $date }}
@endsection

@section('content')
<div class="row">
        <div class="container welcome col-11">
        <div class="d-flex justify-content-between">
        <h3>تفاصيل المحجوزين ليوم {{ $date }}</h3>
            </div>
        </div>
    </div>

    <br>
       
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
                    <th>الترتيب</th>
                    <th>الاسم</th>
                    <th>القيمة</th>
                    <th>التاريخ</th>
                    <th>اليوم</th>
                    <th>نوع الحجز</th>
                    <th>الرتبة</th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#users-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: '{{ route("prisoners.details.data", ["subDepartmentId" => $subDepartmentId, "date" => $date]) }}'
    },
    columns: [
        { data: null, name: 'order', orderable: false, searchable: false },
        { data: 'name', name: 'name' },
        { data: 'amount', name: 'amount' },
        { data: 'date', name: 'date' },
        { data: 'day', name: 'day' },
        { data: 'type', name: 'type' },
        { data: 'grade', name: 'grade' },
    ],
    createdRow: function(row, data, dataIndex) {
        $('td', row).eq(0).html(dataIndex + 1);
    }
});
        });
    </script>
@endpush
