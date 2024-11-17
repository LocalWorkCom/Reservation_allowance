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


@section('title', ' {{ $departmentName }} تفاصيل الموظفين للإدارة')

@section('content')
    <div class="row">
    <div class="container welcome col-11">
    <div class="d-flex justify-content-between">
        <h4>تفاصيل الموظفين للإدارة: {{ $departmentName }}</h4>
        </div>
        </div>
    </div>

    <div class="row">
        <div class="container col-11 mt-3">
            <!-- DataTable -->
            <div class="bg-white p-4">
                <table id="users-table" class="display table table-bordered table-hover">
                    <thead>
                        <tr>
                    <th>#</th>
                    <th>رقم الملف</th>
                    <th>الاسم</th>
                    <th>الرتبة</th>
                    <th>الأيام</th>
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
                url: '{{ route("department.employees.getData", $departmentId) }}',
                data: {
                    month: '{{ $month }}',
                    year: '{{ $year }}',
                }
            },
            columns: [
                { data: null, searchable: false, orderable: false },
                { data: 'file_number', name: 'file_number' },
                { data: 'name', name: 'name' },
                { data: 'grade', name: 'grade' },
                { data: 'days', name: 'days' },
                { data: 'allowance', name: 'allowance' },
            ],
            order: [[1, 'asc']],
                language: {
                    sSearch: "",
                    sSearchPlaceholder: "بحث",
                    sInfo: 'اظهار صفحة _PAGE_ من _PAGES_',
                    sInfoEmpty: 'لا توجد بيانات متاحه',
                    sInfoFiltered: '(تم تصفية من _MAX_ اجمالى البيانات)',
                    sLengthMenu: 'اظهار _MENU_ عنصر لكل صفحة',
                    sZeroRecords: 'نأسف لا توجد نتيجة',
                    oPaginate: {
                        sFirst: '<i class="fa fa-fast-backward"></i>',
                        sPrevious: '<i class="fa fa-chevron-left"></i>',
                        sNext: '<i class="fa fa-chevron-right"></i>',
                        sLast: '<i class="fa fa-step-forward"></i>',
                    },
                },
                pagingType: "full_numbers",
                createdRow: function (row, data, dataIndex) {
                    $('td', row).eq(0).html(dataIndex + 1);
                },
            });
        });
</script>
@endpush
