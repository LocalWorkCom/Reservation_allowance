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
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer>
    </script>
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
                            <div class="col-6"><p> بدل الحجز</p></div>
                            <div class="col-6">{{-- @if (Auth::user()->hasPermission('create reservation_allowances')) --}}
                                <button type="button" class="btn-all" onclick="window.location.href='{{ route('reservation_allowances.create') }}'"
                                    style="color: #0D992C;">

                                    اضافة بدل حجز جديد  <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">
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
        <div class="container  col-11 mt-3 p-0  pt-5 pb-4">

            <div class="col-lg-12">
                <div class="bg-white">
                    @if (session()->has('message'))
                        <div class="alert alert-info">
                            {{ session('message') }}
                        </div>
                    @endif
                    <div>
                        <table id="users-table"
                            class="display table table-responsive-sm  table-bordered table-hover dataTable">
                            <thead>
                                <tr>
                                    <th>الاسم</th>
                                    <th style="width:150px;">العمليات</th>
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
                    url: '{{ route("getAll") }}',
                }, // Correct URL concatenation
                columns: [{
                        data: 'government_id',
                        name: 'government_id'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        sWidth: '100px',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [1, 'desc']
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
                        "sFirst": '<i class="fa fa-fast-backward" aria-hidden="true"></i>', // This is the link to the first page
                        "sPrevious": '<i class="fa fa-chevron-left" aria-hidden="true"></i>', // This is the link to the previous page
                        "sNext": '<i class="fa fa-chevron-right" aria-hidden="true"></i>', // This is the link to the next page
                        "sLast": '<i class="fa fa-step-forward" aria-hidden="true"></i>' // This is the link to the last page
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
                                    console.log('Page '+this.api().page.info().pages)
                                        var page=this.api().page.info().pages;
                                        console.log($('#users-table tr').length);
                                        if (page ==1) {
                                         //   $('.dataTables_paginate').hide();//css('visiblity','hidden');
                                            $('.dataTables_paginate').css('visibility', 'hidden');  // to hide

                                        }
                                    }
            });


        });
    </script>
@endpush
