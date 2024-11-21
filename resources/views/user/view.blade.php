@extends('layout.main')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
<link rel="stylesheet" href=
"https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
<script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js" defer></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer>
</script>
@section('content')
@section('title')
    عرض
@endsection

<section>
    <div class="row">
        <div class="modal fade" id="TranferMdel" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header d-flex justify-content-center">
                        <div class="title d-flex flex-row align-items-center">
                            <h5 class="modal-title"> الغاء التعيين</h5>
                            <img src="{{ asset('frontend/images/group-add-modal.svg') }}" alt="">

                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close">&times;</button>
                    </div>
                    <div class="modal-body mt-3 mb-3">
                        <div class="container pt-5 pb-2" style="border: 0.2px solid rgb(166, 165, 165);">
                            <form id="transfer-form" action="{{ route('user.unsigned') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id_employee" id="id_employee" value="">
                                <div class="mb-3">
                                    <label style="justify-content: flex-end;"> هل انت متاكد من الغاء التعييين
                                        ؟ </label>
                                </div>
                                <div class="text-end pt-3">
                                    <button type="button" class="btn-all p-2 "
                                        style="background-color: transparent; border: 0.5px solid rgb(188, 187, 187); color: rgb(218, 5, 5);"
                                        data-bs-dismiss="modal" aria-label="Close" data-bs-dismiss="modal">

                                        لا
                                    </button>
                                    <button type="submit" class="btn-all mx-2 p-2"
                                        style="background-color: #274373; color: #ffffff;">

                                        نعم
                                    </button>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container welcome col-11">
            <div class="d-flex ">
                @if (request()->fullUrlIs('*employees?department_id=*'))
                    <p>قوة وكيل الامن العام</p>
                @elseif (Auth::user()->rule_id != 2)
                    <p>موظفين القوة</p>
                @elseif (Auth::user()->rule_id == 2)
                    <p>موظفين الوزارة</p>
                @endif

                <div class="form-group">

                    @if (Auth::user()->hasPermission('add_employee User'))

                    <a href="{{ route('download-template') }}" class="btn-all text-info mx-2 p-2">تحميل
                        القالب</a>

                    <button type="button" class="wide-btn mx-2"
                        onclick="window.location.href='{{ route('user.create') }}'" style="color: #0D992C;">
                        اضافة موظف جديد <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">
                    </button>
        

                     </div>
                        @if (request()->fullUrlIs('*employees?department_id=*'))

                            <form class="" action="{{ route('user.employees.add') }}" method="post">
                                <input type="hidden" id="department_id" name="department_id" value="{{request()->get('department_id')}}" class="form-control" >
                                @csrf
                                <div class="row d-flex flex-wrap ">
                                    <!-- 1 for sector , 2 for department -->
                                    <div class="d-flex">
                                        <input type="text" id="Civil_number" name="Civil_number" class="form-control" placeholder="الرقم المدني" style="border-radius:10px !important;">
                                    </div>
                                    <button class="btn-all py-2 mx-2" type="submit" style="color:green;">
                                        <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">
                                        اضافة موظف للادارة
                                    </button>

                               
                            </form>
                        @endif


                    @endif

                </div>

            </div>

        </div>
        <div class="row mb-4">
            <div class="col-12">

            </div>
        </div>





    </div>


    <br>


    <div class="row">

        <div class="container  col-11 mt-3 p-0 ">

            {{-- <form action="{{ route('import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="d-flex  mt-4" dir="rtl">
                    <div class="d-flex form-group">
                        <label for="file" style="color: #555;    font-weight: 700;
    line-height: 48.78px;">اختر
                            الملف المراد استيراده :</label>
                        <input type="file" name="file" class="form-control mb-2" accept=".csv, .xlsx"
                            style="    border: 1px solid #27437324;  border-radius: 5px;background-color: transparent;width: 220px;">
                    </div>
                    <div class="d-flex">
                        <button type="submit"
                            class="btn mx-2 px-4"style="border-radius: 5px;background-color: #274373; border-color: #274373; color:white; border-radius:10px; height:40px;">استيراد</button>
                    </div>
                </div>
            </form> --}}
            {{-- <form action="{{ route('user.employees') }}" method="GET">
                <div class="form-group col-md-5 mx-2">
                    <label for="department_id"> الادارة</label>

                    <select id="department_id" name="department_id" class="form-control select2" placeholder="الادارة">
                        <option>اختار من القائمة</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}">
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex">
                    <button type="submit"
                        class="btn mx-2 px-4"style="border-radius: 5px;background-color: #274373; border-color: #274373; color:white; border-radius:10px; height:40px;">بحث</button>
                </div>
            </form> --}}

            @include('inc.flash')
            <div class="col-lg-12 pt-5 pb-5">
                <div class="bg-white ">

                    <div>
                        <table id="users-table"
                            class="display table table-responsive-sm  table-bordered table-hover dataTable">
                            <thead>
                                <tr>
                                    <th>رقم المسلسل</th>
                                    <th>الرتبه</th>
                                    <th>الاسم</th>
                                    <th>رقم الملف</th>
                                    <th>الرقم المدني</th>
                                    <th>الهاتف</th>
                                    <th>الادارة</th>
                                    <th>القطاع</th>
                                    <th style="width:150px !important;">العمليات</th>
                                </tr>
                            </thead>
                        </table>




                        <script>
                            $(document).ready(function() {
                                $.fn.dataTable.ext.classes.sPageButton = 'btn-pagination btn-sm'; // Change Pagination Button Class

                                @php
                                    $department_id = request()->get('department_id');
                                    $parent_department_id = request()->get('parent_department_id');
                                    $sector_id = request()->get('sector_id');
                                    $type = request()->get('type');
                                    $Civil_number = request()->get('Civil_number'); // Get Civil_number from request
                                    $Dataurl = 'api.users'; // Default URL

                                    if (isset($mode) && $mode == 'search') {
                                        $Dataurl = 'search.user';
                                    }

                                    $parms = [];
                                    if ($department_id) {
                                        $parms['department_id'] = $department_id;
                                    }
                                    if ($parent_department_id) {
                                        $parms['parent_department_id'] = $parent_department_id;
                                    }
                                    if ($sector_id) {
                                        $parms['sector_id'] = $sector_id;
                                    }
                                    if ($Civil_number) {
                                        $parms['Civil_number'] = $Civil_number; // Add Civil_number to parameters
                                    }
                                @endphp
                                /*
                                  $('#users-table tfoot th').each(function (i) {
                                      var title = $('#users-table thead th')
                                          .eq($(this).index())
                                          .text();
                                      $(this).html(
                                          '<input type="text" placeholder="' + title + '" data-index="' + i + '" />'
                                      );
                                  }); */
                                var table = $('#users-table').DataTable({
                                    processing: true,
                                    serverSide: true,
                                    ajax: '{{ route($Dataurl, $parms) }}' +
                                        '{{ isset($q) ? '/' . $q : '' }}', // Correct URL concatenation
                                    bAutoWidth: false,

                                    columns: [{
                                            data: null, // Using 'null' here as we will populate it manually
                                            sWidth: '50px',
                                            orderable: false,
                                            searchable: false,
                                            render: function(data, type, row, meta) {
                                                return meta.row + 1; // Display the sequential index
                                            }
                                        },
                                        // {
                                        //     data: 'id',
                                        //     sWidth: '50px',
                                        //     name: 'id'
                                        // },
                                        {
                                            data: 'grade',
                                            name: 'grade'
                                        },
                                        {
                                            data: 'name',
                                            name: 'name'
                                        },
                                        /*     {
                                                data: 'Civil_number',
                                                name: 'Civil_number'
                                            },
                                            {
                                                data: 'military_number',
                                                name: 'military_number'
                                            }, */
                                        {
                                            data: 'file_number',
                                            name: 'file_number'
                                        },
                                        {
                                            data: 'Civil_number',
                                            name: 'Civil_number'
                                        },
                                        {
                                            data: 'phone',
                                            name: 'phone'
                                        },
                                        {
                                            data: 'department',
                                            name: 'department'
                                        },
                                        {
                                            data: 'sector',
                                            name: 'sector'
                                        },
                                        {
                                            data: 'action',
                                            name: 'action',

                                            sWidth: '200px',
                                            orderable: false,
                                            searchable: false
                                        }
                                    ],
                                    columnDefs: [{
                                        targets: -1,
                                        render: function(data, type, row) {
                                            // Using route generation correctly in JavaScript
                                            var useredit = '{{ route('user.edit', ':id') }}';
                                            useredit = useredit.replace(':id', row.id);
                                            var usershow = '{{ route('user.show', ':id') }}';
                                            usershow = usershow.replace(':id', row.id);
                                            var vacation = '{{ route('vacations.list', ':id') }}';
                                            vacation = vacation.replace(':id', row.id);
                                            var unsigned = '{{ route('user.unsigned', ':id') }}';
                                            unsigned = unsigned.replace(':id', row.id);
                                            var visibility = row.department_id != null ? 'd-block-inline' :
                                                'd-none';

                                            return `
        <a href="` + usershow + `" class="btn btn-sm" style="background-color: #274373;"> 
            <i class="fa fa-eye"></i> عرض  
        </a>
        <a href="` + useredit + `" class="btn btn-sm" style="background-color: #F7AF15;"> 
            <i class="fa fa-edit"></i> تعديل 
        </a>
        <a class="btn btn-sm ${visibility}" style="background-color: #E3641E;" onclick="openTransferModal(${row.id})">
            <i class="fa-solid fa-user-tie"></i>  الغاء التعيين
        </a>
    `;
                                        }


                                    }],
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
                                        console.log('Page ' + this.api().page.info().pages)
                                        var page = this.api().page.info().pages;
                                        console.log($('#users-table tr').length);
                                        if (page <= 1) {
                                            //   $('.dataTables_paginate').hide();//css('visiblity','hidden');
                                            $('.dataTables_paginate').css('visibility', 'hidden'); // to hide

                                        }
                                    }
                                });

                                $(table.table().container()).on('keyup', 'tfoot input', function() {
                                    table
                                        .column($(this).data('index'))
                                        .search(this.value)
                                        .draw();
                                });
                            });
                        </script>


                    </div>
                </div>
            </div>

        </div>

    </div>
</section>


<script>
    

    function openTransferModal(id) {
        $('#TranferMdel').modal('show');
        document.getElementById('id_employee').value = id;
    }
    $(document).ready(function() {

        $('#print-table').on('click', function() {
            // Clone the DataTable to a new window
            var printWindow = window.open('', '', 'width=900,height=600');

            // Get the content of the table
            var tableContent = document.getElementById('users-table').outerHTML;

            // Format the content for printing
            printWindow.document.write('<html><head><title>طباعة الجدول</title>');
            printWindow.document.write(
                '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">'
            );
            printWindow.document.write(
                '<style>table { width: 100%; margin: 20px; border-collapse: collapse; } th, td { padding: 8px 12px; border: 1px solid #ddd; } th { background: #f4f4f4; }</style>'
            );
            printWindow.document.write('</head><body dir="rtl">');
            printWindow.document.write('<h3 style="text-align:center;">جدول الموظفين</h3>');
            printWindow.document.write(tableContent); // Write the table HTML into the print window
            printWindow.document.write('</body></html>');
            printWindow.document.close(); // Close the document for printing

            // Wait for the document to be fully loaded before printing
            printWindow.onload = function() {
                printWindow.print();
                printWindow.close();
            };
        });
    });

    function openAndPrint(url) {
        // var url = "${urls.printReturn}?id=" + encodeURIComponent(rowId);
        // فتح الصفحة في نافذة جديدة
        var newWindow = window.open(url, '_blank');
        newWindow.onload = function() {
            newWindow.print();
        };
    }

    function printPDF() {
        // let civil_number = $('#civil_number').val();
        // let start_date = $('#start_date').val();
        // let end_date = $('#end_date').val();

        // Redirect to the print route with search parameters
        window.open('{{ route('print-users') }}', '_blank');
    }
</script>
@endsection
