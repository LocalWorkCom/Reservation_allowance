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


            <div class="d-flex justify-content-between">
                <div class="col-12">
                    <div class="row d-flex " style="direction: rtl">
                        <div class="col-2">
                            <p> بدل حجز بالهويات</p>
                        </div>
                        
                        <form class="" id="search_employee_allowances">
                            @csrf
                            <div class="row d-flex flex-wrap justify-content-between">
                                <!-- 1 for sector , 2 for department -->
                                <input name="department_type" id="department_type" type="hidden"
                                    value="{{ Auth::user()->department_id == null ? 1 : 2 }}">

                                <div class="d-flex mx-2">
                                    {{-- @if (Auth::user()->hasPermission('create reservation_allowances')) --}}
                                    <!-- <label for="Civil_number" class="d-flex "> <i class="fa-solid fa-asterisk" style="color:red; font-size:10px;"></i>اختار </label> -->
                                    <select class="custom-select custom-select-lg select2" name="sector_id" id="sector_id" required>
                                        <option value="0" selected>اختار القطاع</option>
                                        @foreach ($sectors as $sector)
                                            <option value="{{ $sector->id }}" {{$sector->id == $sector_id ? "selected" : ""}}> {{ $sector->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="d-flex mx-2" id="departement_div">
                                    {{-- @if (Auth::user()->hasPermission('create reservation_allowances')) --}}
                                    <!-- <label for="Civil_number" class="w-75"> <i class="fa-solid fa-asterisk" style="color:red; font-size:10px;"></i>اختار الادارة</label> -->
                                    <select class="custom-select custom-select-lg" name="departement_id" id="departement_id">
                                        <option value="0" >اختار الادارة</option>
                                        @if($get_departements)
                                            @foreach($get_departements as $departement)
                                            <option value="{{ $departement->id }}" {{$departement->id == $department_id ? "selected" : ""}}> {{ $departement->name }}</option>
                                                @if(count($departement->children))
                                                    @include('reservation_allowance.manageChildren', [
                                                    'children' => $departement->children,
                                                    'parent_id' => $departement_id,
                                                    ])
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <div class="form-group  mx-2">
                                    <input class="form-control" type="date" name="date" id="date" max="{{$to_day}}" value="{{$to_day}}" required>
                                </div>

                                <!-- <div class="">
                                        <button class="btn-all py-2 px-2" type="submit" style="color:green;">
                                            <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">
                                            عرض موظفين بدل حجز </button>
                                </div>-->
                            </div>
                        </form>
                        <!--  <div class="d-flex justify-content-between mt-2">
                                <div class=" mx-2">
                                    {{-- @if (Auth::user()->hasPermission('create reservation_allowances')) --}}
                                    <a class="btn-all py-2 px-2 " href="{{ route('reservation_allowances.create') }}"
                                        style="color: #0D992C;">
                                        <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">
                                        اضافة بدل حجز جديد
                                    </a>
                                    {{-- @endif --}}
                                </div> -->
                        
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
        <div class="container  col-11 mt-3 p-0  pt-5 pb-4">

            <div class="col-lg-12">
                <div class="bg-white">
                    @if (session()->has('message'))
                        <div class="alert alert-info">
                            {{ session('message') }}
                        </div>
                    @endif

                    @if($employee_new_add)
                    <div class="row" style="direction:rtl">
                        <div class="col-lg-4">
                            <h4 style="text-align:center">الموظفين الذين سيتم اضافتهم</h4>
                            <table class="display table table-responsive-sm  table-bordered table-hover dataTable">
                                <thead>
                                    <tr>
                                        <th>الترتيب</th>
                                        <th>الاسم</th>
                                        <th>رقم الملف</th>
                                    </tr>
                                </thead>
                                        @foreach($employee_new_add as $K_employee_newadd=>$employee_newadd)
                                        <tr>
                                            <td>{{$K_employee_newadd+1}}</td>
                                            <td>{{$employee_newadd->name}}</td>
                                            <td>{{$employee_newadd->file_number}}</td>
                                        </tr>
                                        @endforeach
                            </table>   
                        </div>
                        @endif

                        @if($employee_not_dept)
                        <div class="col-lg-4">
                            <h4 style="text-align:center">الموظفين غير مسجلين فى الادارة او القطاع</h4>
                            <table class="display table table-responsive-sm  table-bordered table-hover dataTable">
                                <thead>
                                    <tr>
                                        <th>الترتيب</th>
                                        <th>الاسم</th>
                                        <th>رقم الملف</th>
                                    </tr>
                                </thead>
                                        @foreach($employee_not_dept as $K_employee_notdept=>$employee_notdept)
                                        <tr>
                                            <td>{{$K_employee_notdept+1}}</td>
                                            <td>{{$employee_notdept->name}}</td>
                                            <td>{{$employee_notdept->file_number}}</td>                                        
                                        </tr>
                                        @endforeach
                            </table>   
                        </div>
                        @endif

                        @if($employee_not_found)
                        <div class="col-lg-4">
                            <h4 style="text-align:center">الموظفين ارقام الملفات خطاء</h4>
                            <table class="display table table-responsive-sm  table-bordered table-hover dataTable">
                                <thead>
                                    <tr>
                                        <th>الترتيب</th>
                                        <th>رقم الملف</th>
                                    </tr>
                                </thead>
                                    @foreach($employee_not_found as $K_employee_notfound=>$employee_notfound)
                                    <tr>
                                    <td>{{$K_employee_notfound+1}}</td>
                                    <td>{{$employee_notfound['Civil_number']}}</td>
                                    </tr>
                                    @endforeach
                            </table>   
                        </div>
                        @endif


                        <div class="col-lg-12" style="text-align: right">
                            <form method="post" action="{{ route('reservation_allowances.store.all') }}">
                                @csrf
                                <input type="hidden" name="date" value="{{$to_day}}">
                                <input type="hidden" name="type" value="{{$type}}">
                                <input type="hidden" name="sector_id" value="{{$sector_id}}">
                                <input type="hidden" name="departement_id" value="{{$department_id}}">
                                <button class="btn-all py-2 px-2" type="submit" style="color: #0D992C;">اضف بدل حجز</button>
                                <button class="btn-all py-2 px-2" type="button" onclick="history.back()" style="color: #0D992C;">الغاء</button>
                            </from>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <script>
        $(".select2").select2({
            dir: "rtl"
        });
        $('#sector_id').on('select2:select', function(e) {
            // alert('select');
            // var managerId = $(this).val();
            var sectorid = $(this).val();
            var department_type = document.getElementById('department_type').value;
            var map_url = "{{ route('reservation_allowances.get_departement', ['id', 'type']) }}";
            map_url = map_url.replace('id', sectorid);
            map_url = map_url.replace('type', department_type);
            $.get(map_url, function(data) {
                $("#departement_id").html(data);
                // initDept()
                $('#departement_id').val(0).trigger('change');

            });
        });

        function initDept() {
            $("#departement_id").select2({
                dir: "rtl"
            });
        }
    </script>



@endsection
@push('scripts')

    <script>


        $(document).ready(function() {
            var table = "";
            var sector_id = document.getElementById('sector_id').value;
            var departement_id = document.getElementById('departement_id').value;
            var date = document.getElementById('date').value;
            var filter = 'all'; // Default filter

                table = $('#users-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('reservation_allowances.getAll') }}",
                        data: function(d) {
                            d.filter = filter; // Use the global filter variable
                            d.sector_id = sector_id;
                            d.departement_id = departement_id;
                            d.date = date;
                        }
                    },
                    columns: [{
                            data: null,
                            name: 'order', orderable: false, searchable: false
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
                            data: 'employee_allowance_type_btn',
                            name: 'employee_allowance_type_btn'
                        },
                        {
                            data: 'employee_allowance_amount',
                            name: 'employee_allowance_amount'
                        } 
                    ],
                    order: [0, 'asc'],

                    oLanguage: {
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
                    pagingType: "full_numbers",
                    bDestroy: true,
                    fnDrawCallback: function(oSettings) {
                        console.log('Page ' + this.api().page.info().pages)
                        var page = this.api().page.info().pages;
                        console.log($('#users-table tr').length);
                        if (page <= 1) {
                            //$('.dataTables_paginate').hide();//css('visiblity','hidden');
                            $('.dataTables_paginate').css('visibility', 'hidden'); // to hide
                        }
                    },
                    createdRow: function(row, data, dataIndex) {
                        $('td', row).eq(0).html(dataIndex + 1); // Automatic numbering in the first column
                    }
                });
                $('.btn-filter').on('click', function() {
                    filter = $(this).data('filter'); // Get the filter value from the clicked button
                    table.ajax.reload(); // Reload the DataTable with the new filter
                });
                // Filter buttons click event
                $('.btn-filter').click(function() {
                    filter = $(this).data('filter'); // Update filter
                    $('.btn-filter').removeClass('btn-active'); // Remove active class from all
                    $(this).addClass('btn-active'); // Add active class to clicked button

                    table.page(0).draw(false); // Reset to first page and redraw the table
                });
                //end of call datatable
            


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
