<style>
.div-info {
    border-radius: 10px;
    padding: 20px;
    margin-top: 20px;
    width: 200px;
    height: 150px;
    background-color: #27437329;
}
.div-info-padding{
    padding: 3px 0;
    direction: initial;
}
</style>

@extends('layout.main')
@push('style')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js" defer></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer></script>
@endpush

@section('title')
    القطاعات
@endsection

@section('content')
    <div class="row">
        <div class="container welcome col-11" style="height: auto !important">
            <div class="d-flex justify-content-between">
                <div class="col-12">
                    <div class="row" style="direction: rtl">
                        <div class="col-6">
                            <p> احصائيات بدل حجز</p>
                        </div>
                        <div class="col-6">
                            {{-- @if (Auth::user()->hasPermission('create reservation_allowances')) --}}
                            <button type="button" class="btn-all" onclick="window.location.href='{{ route('reservation_allowances.create') }}'" style="color: #0D992C;">
                                اضافة بدل حجز جديد <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">
                            </button>
                            {{-- @endif --}}
                        </div>

                        <div class="col-12 div-info">
                            <div class="row">
                                <div class="col-6 div-info-padding"><b>القطاع : امن عام</b></div>
                                <div class="col-6 div-info-padding"><b>الادارة الرئيسية : مديرية امن حولى</b></div>
                                <div class="col-6 div-info-padding"><b>الادارة الفرعية : مخفر النقرة</b></div>
                                <div class="col-6 div-info-padding"><b>مبلغ بدل الحجز : 1200 دينار</b></div>
                                <div class="col-6 div-info-padding"><b>اليوم : السبت</b></div>
                                <div class="col-6 div-info-padding"><b>التاريخ : 27/9/2024</b></div>
                                <div class="col-6 div-info-padding"><b>عدد العسكرين المحجوزين : 3</b></div>
                            </div>
                        </div>
                    </div>
                </div>
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
                                    <th>الترتيب</th>  <!-- Automatic numbering -->
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
                    url: '{{ route("Reserv_statistic.getAll") }}',  // Route to fetch data for the table
                },
                columns: [
                    { data: 'department_name', name: 'department_name' }, // اسم الادارة (Department Name)
                    { data: 'sub_departments_count', name: 'sub_departments_count' }, // عدد الادارات الفرعية (Sub-Departments Count)
                    { data: 'reservation_allowance_budget', name: 'reservation_allowance_budget' }, // ميزانية بدل الحجز (Reservation Budget)
                    { data: 'registered_by', name: 'registered_by' }, // المسجل (Registered By)
                    { data: 'remaining_amount', name: 'remaining_amount' }, // المبلغ المتبقى (Remaining Amount)
                    { data: 'number_of_employees', name: 'number_of_employees' }, // عدد الموظفين (Number of Employees)
                    { data: 'received_allowance_count', name: 'received_allowance_count' }, // الحاصلين على بدل حجز (Received Allowance Count)
                    { data: 'did_not_receive_allowance_count', name: 'did_not_receive_allowance_count' } // لم يحصل على بدل حجز (Did Not Receive Allowance Count)
                ],
                order: [
                    [0, 'asc']
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
                        $('.dataTables_paginate').css('visibility', 'hidden'); // Hide pagination if only one page
                    }
                }
            });
        });
    </script>
@endpush
