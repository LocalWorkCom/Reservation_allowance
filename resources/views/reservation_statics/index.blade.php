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
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer>
    </script>
@endpush

@section('title')
    القطاعات
@endsection

@section('content')
    <div class="row">
        <div class="container welcome col-11">
        <div class="d-flex justify-content-between">
                <p>الاحصائيات بدل حجز - {{ $sector_name }}</p>
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
                        <table id="users-table"
                            class="display table table-responsive-sm table-bordered table-hover dataTable">
                            <thead>
                                <tr>
                                    <th>الترتيب</th>
                                    <th>اسم الادارة</th>
                                    <th>عدد الادارات الفرعية</th>
                                    <th>ميزانية بدل الحجز</th>
                                    <th>المسجل</th>
                                    <th>المبلغ المتبقى</th>
                                    <th>عدد الموظفين</th>
                                    <th>الحاصلين على بدل حجز</th>
                                    <th>لم يحصل على بدل حجز</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $.fn.dataTable.ext.classes.sPageButton = 'btn-pagination btn-sm';

            $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('Reserv_statistic.getAll', ['sector_id' => $sector_id]) }}', 
                },
                columns: [{
                        data: null,
                        name: 'order',
                        orderable: false,
                        searchable: false
                    },
                   
                    {
                        data: 'department_name',
                        name: 'department_name'
                    },
                    {
                        data: 'sub_departments_count',
                        name: 'sub_departments_count'
                    },
                    {
                        data: 'reservation_allowance_budget',
                        name: 'reservation_allowance_budget'
                    },
                    {
                        data: 'registered_by',
                        name: 'registered_by'
                    },
                    {
                        data: 'remaining_amount',
                        name: 'remaining_amount'
                    },
                    {
                        data: 'number_of_employees',
                        name: 'number_of_employees'
                    },
                    {
                        data: 'received_allowance_count',
                        name: 'received_allowance_count'
                    },
                    {
                        data: 'did_not_receive_allowance_count',
                        name: 'did_not_receive_allowance_count'
                    }
                ],
                order: [
                    [1, 'asc']
                ],
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
                pagingType: "full_numbers",
                fnDrawCallback: function(oSettings) {
                    var page = this.api().page.info().pages;
                    if (page == 1) {
                        $('.dataTables_paginate').css('visibility',
                        'hidden'); // Hide pagination if only one page
                    }
                },
                createdRow: function(row, data, dataIndex) {
                    $('td', row).eq(0).html(dataIndex + 1);
                }
            });
        });
    </script>
@endpush
