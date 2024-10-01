<style>
.div-info {
    border-radius: 10px;
    padding: 20px;
    margin-top: 20px;
    width: 200px;
    height: 200px;
    background-color: #F6F7FD;
    border: 1px solid #D9D9D9 !important;
}

.div-info-padding {
    padding: 3px 0;
    direction: initial;
    font-family: Almarai;
    font-size: 24px;
    font-weight: 700;
    line-height: 36px;
    text-align: right;

}

.div-info-padding b span {
    color: #032F70;
}
.paragraph{
    display: flex;
    justify-content: end;
    font-weight: 700;
    font-size: 25px;
}
</style>@extends('layout.main')

@push('style')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
<script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js" defer></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer>
</script>
@endpush

@section('title')
رصيد بدل حجز
@endsection

@section('content')
<div class="row">
    <div class="container  col-11 pb-3" style="height: auto !important;">
        <div class="d-flex justify-content-between">
            <div class="col-12">
                <div class="row" style="direction: rtl">
                <p class="paragraph mt-3"> رصيد بدل حجز</p>



                    <div class="col-12 div-info d-flex justify-content-between">
                        <div class="col-7">
                            <div class="col-12 div-info-padding"><b>القطاع : <span>{{ $sector }}</span></b></div>
                            <div class="col-12 div-info-padding"><b>الادارة الرئيسية :
                                    <span>{{ $mainDepartment }}</span></b></div>
                            <div class="col-12 div-info-padding"><b>الادارة الفرعية :
                                    <span>{{ $subDepartment }}</span></b>
                            </div>
                            <div class="col-12 div-info-padding"><b>التاريخ : <span>{{ $today }}</span></b></div>
                        </div>
                        <div class="col-5">
                            <div class="col-12 div-info-padding"><b>الشهر : <span>{{ $currentMonth }}</span></b></div>
                            <div class="col-12 div-info-padding"><b>ميزانيه بدل الحجز :
                                    <span>{{ number_format($reservationAllowanceBudget, 2) }}</span></b></div>
                            <div class="col-12 div-info-padding"><b>مبالغ مسجله :
                                    <span>{{ number_format($recordedAmounts, 2) }}</span></b></div>
                            <div class="col-12 div-info-padding"><b>المتبقي :
                                    <span>{{ number_format($remainingAmount, 2) }}</span></b></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<br>

<div class="row">
    <div class="container  col-11 mt-3 p-0 pt-5 pb-4">
        <div class="col-lg-12">
            <div class="bg-white">
                <h3 class="paragraph">احصائيات بدل حجز شهر {{ $currentMonth }}</h3>
                <table id="credit-table" class="display table table-responsive-sm table-bordered table-hover dataTable">
                    <thead style="    text-align: center; background-color: #f0f8ff8a; font-weight: 700;">
                        <tr>
                            <th class="text-center">الترتيب</th>
                            <th class="text-center">اليوم</th>
                            <th class="text-center">عدد المحجوزين</th>
                            <th class="text-center">حجز جزئي العدد</th>
                            <th class="text-center">حجز جزئي المبلغ</th>
                            <th class="text-center">حجز كلي العدد</th>
                            <th class="text-center">حجز كلي المبلغ</th>
                            <th class="text-center">اجمالي المبلغ</th>
                            <th class="text-center">طباعة</th>
                        </tr>
                    </thead>
                </table>
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
        columns: [{
                data: null,
                name: 'order',
                orderable: false,
                searchable: false
            }, // Automatic numbering column
            {
                data: 'day',
                name: 'day'
            },
            {
                data: 'prisoners_count',
                name: 'prisoners_count'
            },
            {
                data: 'partial_reservation_count',
                name: 'partial_reservation_count'
            },
            {
                data: 'full_reservation_count',
                name: 'full_reservation_count'
            },
            {
                data: 'partial_reservation_amount',
                name: 'partial_reservation_amount'
            },
            {
                data: 'full_reservation_amount',
                name: 'full_reservation_amount'
            },
            {
                data: 'total_amount',
                name: 'total_amount'
            },
            {
                data: 'print',
                name: 'print',
                orderable: false,
                searchable: false
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
            $('td', row).eq(0).html(dataIndex + 1); // Automatic numbering in the first column
        }
    });
});

function printReport(date) {
    var printWindow = window.open('/reservation_statics_credit/print?date=' + date, '_blank');
    printWindow.focus();
}
</script>
@endpush