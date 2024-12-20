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

    .custom-select {
        width: 100%;
        color: green !important;
        border-radius: 10px !important;
        height: 43px !important;
        background-color: #fafbfd !important;
    }

    .custom-select-lg {
        /* height: calc(2.45rem + 0px) !important; */
        padding-top: 0.375rem;
        padding-bottom: .375rem;
        font-size: 125%;
        margin-inline: 5px !important;
    }
</style>


@extends('layout.main')
@push('style')
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
    <script type="text/javascript" charset="utf8"
        src="https://code.jquery.com/jquery-3.5.1.js" defer></script>
    <script type="text/javascript" charset="utf8"
        src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer>
    </script>
@endpush

@section('title')
    القطاعات
@endsection
@section('content')
    <div class="row">
        <div class="container welcome col-11">
            <div class="d-flex justify-content-between">

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif


                <p> بدل الحجز</p>
            </div>

            <form class="" id="search_employee_allowances">
                @csrf
                <div class="row d-flex flex-wrap ">
                    <!-- 1 for sector , 2 for department -->
                    <input name="department_type" id="department_type"
                        type="hidden"
                        value="{{ Auth::user()->department_id == null ? 1 : 2 }}">

                    <div class=" mx-1" id="departement_div">
                        {{-- @if (Auth::user()->hasPermission('create reservation_allowances')) --}}
                        <!-- <label for="Civil_number" class="w-75"> <i class="fa-solid fa-asterisk" style="color:red; font-size:10px;"></i>اختار الادارة</label> -->
                        <select class="custom-select custom-select-lg select2"
                            name="departement_id" id="departement_id">
                            <option value="0" selected data-name="">اختار الادارة</option>
                        </select>
                    </div>

                    <div class="mx-1">
                        {{-- @if (Auth::user()->hasPermission('create reservation_allowances')) --}}
                        <!-- <label for="Civil_number" class="d-flex "> <i class="fa-solid fa-asterisk" style="color:red; font-size:10px;"></i>اختار </label> -->
                        <select class="custom-select custom-select-lg select2"
                            name="sector_id" id="sector_id" required>
                            <option value="0" selected data-name="">اختار القطاع</option>
                            @foreach ($sectors as $sector)
                                <option value="{{ $sector->uuid }}" data-name="{{ $sector->name }}">
                                    {{ $sector->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class=" mx-1">
                        <select id="year" name="year"
                            class="custom-select custom-select-lg "
                            style="color:gray !important;">
                            @for ($y = 2024; $y <= date('Y'); $y++)
                                <option value="{{ $y }}"
                                    {{ $y == now()->year ? 'selected' : '' }}>
                                    {{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <input type="hidden" name="current_month" id="current_month">

                </div>
            </form>
        </div>
        <!-- show_reservation_allowances_info -->
        <div id="show_reservation_allowances_info" class="col-12"></div>
        <!-- end of show_reservation_allowances_info -->
    </div>
    </div>
    </div>
    </div>
    </div>

    <br>
    <div class="row">
        <div class="container col-11 p-4">
            <div class="d-flex flex-wrap" dir="rtl" style="background-color:transparent;">
                <h4 id="current_sector"></h4>
                <h4 id="current_department"></h4>
            </div>

            <div class="d-flex flex-wrap" dir="rtl">
                <button class="btn-all px-3 mx-2 mb-3 btn-filter"
                    onclick="search_employee_allowances_with_month(1)">يناير
                </button>

                <button class="btn-all px-3 mx-2 mb-3 btn-filter"
                    onclick="search_employee_allowances_with_month(2)">فبراير
                </button>

                <button class="btn-all px-3 mx-2 mb-3 btn-filter"
                    onclick="search_employee_allowances_with_month(3)">مارس
                </button>

                <button class="btn-all px-3 mx-2 mb-3 btn-filter"
                    onclick="search_employee_allowances_with_month(4)">ابريل
                </button>

                <button class="btn-all px-3 mx-2 mb-3 btn-filter"
                    onclick="search_employee_allowances_with_month(5)">مايو
                </button>

                <button class="btn-all px-3 mx-2 mb-3 btn-filter"
                    onclick="search_employee_allowances_with_month(6)">يونيو
                </button>

                <button class="btn-all px-3 mx-2 mb-3 btn-filter"
                    onclick="search_employee_allowances_with_month(7)">يوليو
                </button>

                <button class="btn-all px-3 mx-2 mb-3 btn-filter"
                    onclick="search_employee_allowances_with_month(8)">اغسطس
                </button>

                <button class="btn-all px-3 mx-2 mb-3 btn-filter"
                    onclick="search_employee_allowances_with_month(9)">سبتمبر
                </button>

                <button class="btn-all px-3 mx-2 mb-3 btn-filter"
                    onclick="search_employee_allowances_with_month(10)">اكتوبر
                </button>

                <button class="btn-all px-3 mx-2 mb-3 btn-filter"
                    onclick="search_employee_allowances_with_month(11)">نوفمبر
                </button>

                <button class="btn-all px-3 mx-2 mb-3 btn-filter"
                    onclick="search_employee_allowances_with_month(12)">ديسمبر
                </button>

                <div>
                    <button class="btn-blue mx-1" onclick="print_reservation()">طباعة</button>
                </div>

            </div>

            <div class="col-lg-12">
                <div class="bg-white">
                    @if (session()->has('message'))
                        <div class="alert alert-info">
                            {{ session('message') }}
                        </div>
                    @endif

                    <div>
                        <table id="users-table"
                            class="display table table-responsive-sm  table-bordered table-hover dataTable" dir="rtl">
                            <thead>
                                <!-- First Row: Group Headers -->
                                <tr>
                                    <th rowspan="2" style="width:5%">م</th>
                                    <th rowspan="2">الرتبة</th>
                                    <th rowspan="2">الاسم</th>
                                    <th rowspan="2">رقم الملف</th>
                                    <th colspan="2">عدد ايام بدل حجز</th>
                                    <th rowspan="2">الاجمالى</th>
                                </tr>

                                <!-- Second Row: Sub-Headers -->
                                <tr>
                                    <th>كلى</th>
                                    <th>جزئى</th>
                                </tr>
                            </thead>
                        </table>

                        <p style=" font-weight: bold; font-size: 20px;color: #274373;margin-top: 10px;" class="mx-2">
                            المجموع الكلي: <span id="total-amount">0.00</span>
                        </p>

                    </div>
                </div>
            </div>


        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(".select2").select2({
            dir: "rtl"
        });
        $('#sector_id').on('select2:select', function(e) {
            // alert('select');
            // var managerId = $(this).val();
            var sectorid = $(this).val();
            var department_type = document.getElementById('department_type').value;
            var current_sector = document.getElementById('current_sector');
            var current_sector_data = $(this).find(':selected').data('name');
            current_sector.innerHTML = current_sector_data;
            var map_url = "{{ route('reservation_allowances.get_departement', ['id', 'type']) }}";
            map_url = map_url.replace('id', sectorid);
            map_url = map_url.replace('type', department_type);
            $.get(map_url, function(data) {
                $("#departement_id").html(data);
                // initDept()
                $('#departement_id').val(0).trigger('change');
            });
        });

        $(document).on("change", "#departement_id", function() {
            var current_department = document.getElementById('current_department');
            var current_department_data = $(this).find(':selected').data('name');
            current_department.innerHTML = current_department_data;
        });

        function initDept() {
            $("#departement_id").select2({
                dir: "rtl"
            });
        }

        //print
        function print_reservation() {

            var reservation_sector_id = document.getElementById('sector_id').value;
            var reservation_departement_id = document.getElementById('departement_id').value;
            if(reservation_sector_id != 0){
                Swal.fire({
                    title: 'تنبيه',
                    text: 'هل انت متاكد من انك تريد ان تطبع بدل حجز لهؤلاء الموظفين',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'نعم, اطبع',
                    cancelButtonText: 'إلغاء',
                    confirmButtonColor: '#3085d6'
                }).then((result) => {
                    if (result.isConfirmed) {
                        var current_month = document.getElementById('current_month').value;
                        var current_year = document.getElementById('year').value;
                        var reservation_sector_id = document.getElementById('sector_id').value;
                        var reservation_departement_id = document.getElementById('departement_id').value;
                        var map_url = "{{ route('reservation_allowances.printReportMonth', ['month', 'year','sector', 'departement']) }}";
                        map_url = map_url.replace('month', current_month);
                        map_url = map_url.replace('year', current_year);
                        map_url = map_url.replace('sector',reservation_sector_id);
                        map_url = map_url.replace('departement',reservation_departement_id);
                        //window.location.href = map_url;
                        window.open(map_url, '_blank');
                    } else {

                    }
                });
            }else{
                Swal.fire({
                    title: 'تنبيه',
                    text: 'يجب ان تختار قطاع اولا',
                    icon: 'warning',
                    showCancelButton: false,
                    confirmButtonText: 'حسنا',
                    cancelButtonText: 'إلغاء',
                    confirmButtonColor: '#3085d6'
                }).then((result) => {
                    
                });
            }
        }
    </script>



@endsection
@push('scripts')
    <script>
        function search_employee_allowances_with_month($month) {
            var sector_id = document.getElementById('sector_id').value;
            if(sector_id != 0){
                get_table_data("{{ route('reservation_allowances.getAllWithMonth') }}",$month);
            }
        }

        $(document).ready(function() {
            var table = "";
            var sector_id = document.getElementById('sector_id').value;
            var departement_id = document.getElementById('departement_id').value;
            var year = document.getElementById('year').value;
            var filter = 'all'; // Default filter

            // Check if there are errors
            @if ($errors->any())
                // Check if it's an add or edit operation
                @if (session('modal_type') === 'add')
                    $('#addForm').modal('show');
                @elseif (session('modal_type') === 'edit')
                    $('#edit').modal('show');
                @endif
            @endif

            $('#search_employee_allowances').on('submit', function(e) {
                var form = $(this);
                e.preventDefault();
                get_table_data(
                    "{{ route('reservation_allowances.getAll', 0) }}"
                    );
            });


            window.get_table_data = function get_table_data(data_url,
            month) {
                var filter = 'all'; // Default filter
                var sector_id = document.getElementById('sector_id').value;
                var departement_id = document.getElementById('departement_id').value;
                var year = document.getElementById('year').value;
                $.fn.dataTable.ext.classes.sPageButton = 'btn-pagination btn-sm'; // Change Pagination Button Class
                table = $('#users-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: data_url,
                        data: function(d) {
                            d.filter =
                            filter; // Use the global filter variable
                            d.sector_id = sector_id;
                            d.departement_id = departement_id;
                            d.year = year;
                            d.month = month;
                        },
                        dataSrc: function(json) {

                            if (json.total_amount) {
                                $('#total-amount').text(json.total_amount);
                            }
                            if (json.current_month) {
                                $('#current_month').val(json.current_month);
                            }
                            return json.data || [];
                        }
                    },
                    columns: [{
                            data: null,
                            name: 'order',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'employee_grade',
                            name: 'employee_grade'
                        },
                        {
                            data: 'employee_name',
                            name: 'employee_name'
                        },
                        {
                            data: 'employee_file_num',
                            name: 'employee_file_num'
                        },
                        {
                            data: 'allowance_all_count_but',
                            name: 'allowance_all_count_but'
                        },
                        {
                            data: 'allowance_part_count_but',
                            name: 'allowance_part_count_but'
                        },
                        {
                            data: 'allowance_sum_but',
                            name: 'allowance_sum_but'
                        }
                    ],
                    order: [0, 'asc'],


                    layout: {

                        bottomEnd: {
                            paging: {
                                firstLast: false
                            }
                        }
                    },
                    bDestroy: true,
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
                    "pagingType": "full_numbers",
                    "fnDrawCallback": function(oSettings) {
                        var api = this.api();
                        var pageInfo = api.page.info();
                        if (pageInfo.recordsTotal <= 10) {
                            $('.dataTables_paginate').css('visibility', 'hidden');
                        } else {
                            $('.dataTables_paginate').css('visibility', 'visible');
                        }
                    },
                    createdRow: function(row, data, dataIndex) {
                        $('td', row).eq(0).html(dataIndex + 1 ); // Automatic numbering in the first column
                    }
                });
                $('.btn-filter').on('click', function() {
                    filter = $(this).data('filter'); // Get the filter value from the clicked button
                    table.ajax
                .reload(); // Reload the DataTable with the new filter
                });
                // Filter buttons click event
                $('.btn-filter').click(function() {
                    filter = $(this).data('filter'); // Update filter
                    $('.btn-filter').removeClass( 'btn-active'); // Remove active class from all
                    $(this).addClass('btn-active'); // Add active class to clicked button
                    table.page(0).draw(
                    false); // Reset to first page and redraw the table
                });
                //end of call datatable
            }

        });
    </script>
    <script>
        $('.c-radio').on('change', function() {
            // Get the selected value
            var selectedValue = $(this).val();
            console.log("Selected option: " + selectedValue);

            // Perform actions based on the selected value
            if (selectedValue === '0') {
                alert("You selected 0");
            } else if (selectedValue === '1') {
                alert("You selected Option 1");
            } else if (selectedValue === '2') {
                alert("You selected Option 2");
            }
        });
    </script>
@endpush
