@extends('layout.main')

@push('style')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js" defer></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer></script>
@endpush

@section('title')
    رصيد بدل حجز
@endsection

@section('content')
    <div class="row">
        <!-- Main Section with heading -->
        <div class="container col-11" style="height: auto !important;">
            <div class="d-flex justify-content-between">
                <div class="col-12">
                    <div class="row" style="direction: rtl">
                        <div class="col-6">
                            <p>رصيد بدل حجز</p>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn-all" onclick="window.location.href='{{ route('reservation_allowances.create') }}'" style="color: #0D992C;">
                                اضافة بدل حجز جديد <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">
                            </button>
                        </div>

                        <!-- Credit Summary Section -->
                        <div class="col-12 div-info">
                            <div class="row">
                                <div class="col-12 div-info-padding"><b>القطاع : امن عام</b></div>
                                <div class="col-12 div-info-padding"><b>الادارة الرئيسية : مديرية امن حولى</b></div>
                                <div class="col-12 div-info-padding"><b>الادارة الفرعية : مخفر النقرة</b></div>
                                <div class="col-12 div-info-padding"><b>مبلغ بدل الحجز : 3000 دينار</b></div>
                                <div class="col-12 div-info-padding"><b>المبلغ المسجل : 1050 دينار</b></div>
                                <div class="col-12 div-info-padding"><b>المبلغ المتبقي : 1950 دينار</b></div>
                                <div class="col-12 div-info-padding"><b>التاريخ : 27/9/2024</b></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <br>

    <!-- Statistics Table Section -->
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
                        <table id="credit-table" class="display table table-responsive-sm table-bordered table-hover dataTable">
                            <thead>
                                <tr>
                                    <th>اليوم</th>
                                    <th>عدد المحجوزين</th>
                                    <th>حجز جزئي العدد</th>
                                    <th>حجز جزئي المبلغ</th>
                                    <th>حجز كلي العدد</th>
                                    <th>حجز كلي المبلغ</th>
                                    <th>اجمالي المبلغ</th>
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

            $('#credit-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("Reserv_statistic_credit.getAll") }}',
                },
                columns: [
                    { data: 'day', name: 'day' },
                    { data: 'prisoners_count', name: 'prisoners_count' },
                    { data: 'partial_reservation_count', name: 'partial_reservation_count' },
                    { data: 'partial_reservation_amount', name: 'partial_reservation_amount' },
                    { data: 'full_reservation_count', name: 'full_reservation_count' },
                    { data: 'full_reservation_amount', name: 'full_reservation_amount' },
                    { data: 'total_amount', name: 'total_amount' }
                ],
                order: [[0, 'asc']],
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
