@extends('layout.main')

@push('style')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js" defer></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer>
    </script>

    <style>
        .div-info {
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            width: 100%;
            background-color: #F6F7FD;
            border: 1px solid #D9D9D9 !important;
        }

        .div-info-padding {
            padding: 3px 0;
            font-family: Almarai;
            font-size: 24px;
            font-weight: 700;
            line-height: 36px;
            text-align: right;
        }

        .div-info-padding b span {
            color: #032F70;
        }

        /* Box around the table */
        .table-box {
            margin-top: 30px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 2px 15px rgba(0, 0, 0, 0.1);
        }

        h3 {
            font-family: Almarai;
            font-size: 22px;
            text-align: center;
            color: #333;
        }

        #search-form input {
            height: 40px;
            border-radius: 10px !important;
            background-color: #f5f6fa;
        }

        #search-form label {
            font-size: 20px;
            margin-inline: 10px;
            font-weight: 700;
        }

        .button-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-top: 10px;
        }
    </style>
@endpush

@section('title')
    البحث عن بيانات الحجز
@endsection

@section('content')
    <div class="container welcome col-11 my-4" dir="rtl">
        <!-- Search Form and Buttons Container -->
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <!-- General Search Form -->
            <form id="search-form" class="d-flex align-items-center me-3" method="get"
                action="{{ route('reservation_fetch.getAll') }}">
                <label for="file_number" class="form-label mx-2 col-3">رقم الملف</label>
                <input type="text" id="file_number" name="file_number" class="form-control" required>

                <button type="submit" class="btn  mx-2"
                    style="    background-color: #b9bcc0; color: #0f0e0e;border-radius: 10px; border: none; font-weight: 700; ">بحث</button>
            </form>

            <!-- Buttons Group -->
            <div class="d-flex gap-2 flex-wrap">
                <!-- Last Month Search Form -->
                <form id="last-month-form" method="get" action="{{ route('reservation_fetch.getLastMonth') }}">
                    <input type="hidden" name="file_number" id="last_month_file_number">
                    <button type="submit" class="btn  mx-1" style="background-color: #3c7327; color:white; border-radius:10px;">الشهر
                        الماضي</button>
                </form>

                <!-- Last 3 Months Search Form -->
                <form id="last-three-month-form" method="get"
                    action="{{ route('reservation_fetch.getLastThreeMonths') }}">
                    <input type="hidden" name="file_number" id="last_three_month_file_number">
                    <button type="submit" class="btn  mx-1" style="background-color: #d06702; color:white; border-radius:10px;">آخر 3
                        شهور</button>
                </form>

                <!-- Last 6 Months Search Form -->
                <form id="last-six-months-form" method="get" action="{{ route('reservation_fetch.getLastSixMonths') }}">
                    <input type="hidden" name="file_number" id="last_six_months_file_number">
                    <button type="submit" class="btn  mx-1" style="background-color:#2c9e9f; color:white; border-radius:10px;">آخر ستة
                        أشهر</button>
                </form>

                <!-- Last Year Search Form -->
                <form id="last-year-form" method="get" action="{{ route('reservation_fetch.getLastYear') }}">
                    <input type="hidden" name="file_number" id="last_year_file_number">
                    <button type="submit" class="btn  mx-1" style="background-color: #c9b22c; color:white; border-radius:10px;">السنة
                        الماضية</button>
                </form>
                <!-- Other Dates Button -->
                <button id="other-dates-button" class="btn" style="background-color: #c47900; color:white; border-radius:10px;">تواريخ

                    أخرى ...</button>
            </div>
         
        </div> 


    </div>
   
   <!-- Date Range Picker (Initially Hidden) -->
   <div id="date-picker-container" class="row col-12 mt-3" style="display: none;">
   <div class="container welcome col-11 my-4" dir="rtl">
                <form id="custom-date-form" class="d-flex align-items-center">
             
                    <label for="start_date" class="form-label mx-2" style="    font-weight: 700;">من</label>
                    <input type="date" id="start_date" name="start_date" class="form-control mx-2" style="background-color: #f5f6fa; !important;     " >
                    <label for="end_date" class="form-label mx-2" style="    font-weight: 700;">إلى</label>
                    <input type="date" id="end_date" name="end_date" class="form-control mx-2" style="background-color: #f5f6fa; !important; " >
                    <button type="submit" class="btn btn-success mx-2" style="    background-color: #b9bcc0; color: #0f0e0e;border-radius: 10px; border: none;font-weight: 700;  ">بحث
                        بالتواريخ</button>
                      
                </form>  </div>
            </div>    
    <!-- </div> -->


<!-- Results Table -->
<div class="container col-11 pb-4" >
    <div class="" >
        <h3 style="font-weight: 700; display: flex; justify-content: flex-end; padding-top: 20px; font-size: 25px;">نتائج البحث</h3>
        <div class="col-md-2 mb-2">
            <button type="button" class="btn" onclick="printPDF()" style="background-color: #274373; color:white;">طباعة</button>
        </div>
        <table id="reservation-table" class="display table table-responsive-sm table-bordered table-hover dataTable">
            <thead>
                <tr>
                    <th>الترتيب</th>
                    <th>اليوم</th>
                    <th>التاريخ</th>
                    <th>الاسم</th>
                    <th>قطاع</th>
                    <th>الادارة</th>
                    <th>نوع</th>  
                    <th>رتبه</th>
                    <th>نوع الحجز</th>
                    <th>القيمة</th>
                </tr>
            </thead>
        </table>
        <p style=" font-weight: bold; font-size: 20px;color: #274373;margin-top: 10px;" class="mx-2">
    المجموع الكلي: <span id="total-amount">0.00</span>
</p>

    </div>
</div>
@endsection

@push('scripts')
    <script>
      $(document).ready(function() {
    let table = null;

    function initializeTable(url) {
        table = $('#reservation-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: url,
                data: function(d) {
                    d.file_number = $('#file_number').val().trim();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                },
                dataSrc: function(json) {
                    if (json.totalAmount) {
                        $('#total-amount').text(json.totalAmount);
                    }
                    return json.data || [];
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        alert(xhr.responseJSON.error);
                    }
                }
            },
            columns: [
                { data: null, name: 'order', orderable: false, searchable: false },
                { data: 'day', name: 'day' },
                { data: 'date', name: 'date' },
                { data: 'name', name: 'name' },
                { data: 'sector', name: 'sector' },
                { data: 'department', name: 'department' },
                { data: 'grade', name: 'grade' },
                { data: 'grade_type', name: 'grade_type' },
                { data: 'type', name: 'type' },
                { data: 'amount', name: 'amount' },
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
                    var api = this.api();
                    var pageInfo = api.page.info();
                    // Check if the total number of records is less than or equal to the number of entries per page
                    if (pageInfo.recordsTotal <= 10) { // Adjust this number based on your page length
                        $('.dataTables_paginate').css('visibility', 'hidden'); // Hide pagination
                    } else {
                        $('.dataTables_paginate').css('visibility', 'visible'); // Show pagination
                    }
                }
        });
    }

    function loadTable(url) {
        const fileNumber = $('#file_number').val().trim();
        if (!fileNumber) {
            alert('Please enter a valid File Number');
            return;
        }
        if (table === null) {
            initializeTable(url);
        } else {
            table.ajax.url(url).load();
        }
    }

    $('#search-form').on('submit', function(e) {
        e.preventDefault();
        loadTable('{{ route('reservation_fetch.getAll') }}');
    });

    $('#last-month-form').on('submit', (e) => {
        e.preventDefault();
        loadTable('{{ route('reservation_fetch.getLastMonth') }}');
    });
    $('#last-three-month-form').on('submit', (e) => {
        e.preventDefault();
        loadTable('{{ route('reservation_fetch.getLastThreeMonths') }}');
    });
    $('#last-six-months-form').on('submit', (e) => {
        e.preventDefault();
        loadTable('{{ route('reservation_fetch.getLastSixMonths') }}');
    });
    $('#last-year-form').on('submit', (e) => {
        e.preventDefault();
        loadTable('{{ route('reservation_fetch.getLastYear') }}');
    });

    $('#other-dates-button').on('click', function() {
        $('#date-picker-container').toggle();
    });
    $('#custom-date-form').on('submit', function(e) {
        e.preventDefault();
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        if (startDate && endDate) {
            loadTable('{{ route('reservation_fetch.getCustomDateRange') }}');
        } else {
            alert('Please select both start and end dates');
        }
    });

    // Print function
    function printPDF() {
        const fileNumber = $('#file_number').val().trim();
        if (fileNumber) {
            window.open('{{ route('reservation_fetch.print') }}?file_number=' + fileNumber, '_blank');
        } else {
            alert('Please enter a valid File Number');
        }
    }

    $('#print-pdf-button').on('click', printPDF);
});


        function printPDF() {
            let file_number = $('#file_number').val();
            window.open('{{ route('reservation_fetch.print') }}' + '?file_number=' + file_number, '_blank');
        }
    </script>
@endpush
