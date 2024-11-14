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

        <div class="container welcome col-11">
            <div class="d-flex justify-content-between">
                @if (Auth::user()->rule_id == 2)
                    <p>موظفين الوزارة</p>
                @endif
                @if (Auth::user()->rule_id != 2)
                    <p>موظفين القوة</p>
                @endif
                <div class="form-group">
                    @if (Auth::user()->hasPermission('add_employee User'))
                        <button type="button" class="wide-btn"
                            onclick="window.location.href='{{ route('user.create') }}'" style="color: #0D992C;">
                            اضافة موظف جديد <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">
                        </button>
                        {{-- <a href="{{ route('export-users') }}" class="btn btn-primary"
                            style="border-radius: 5px;">تصدير</a> --}}
                        {{-- <button type="button" class="btn btn-success" onclick="window.print()"
                            style="background-color: #274373; color:white;">طباعة الجدول</button> --}}

                        <a href="{{ route('download-template') }}" class="btn "
                            style="border-radius: 5px;background-color: #274373; border-color: #274373; color:white; border-radius:10px;">تحميل
                            القالب</a>
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
                                    {{--    <th>رقم المدني</th>
                                    <th>الرقم العسكري</th> --}}
                                    <th>رقم الملف</th>
                                    <th>الهاتف</th>
                                    <th>الادارة</th>
                                    <th>القطاع</th>
                                    <th style="width:150px !important;">العمليات</th>
                                </tr>
                            </thead>
                            <!--  <tfoot>
                                    <tr>
                                        <th>رقم التعريف</th>
                                        <th>الاسم</th>
                                        <th>القسم</th>
                                        <th>الهاتف</th>
                                        <th>الرقم العسكري</th>
                                        <th style="width:150px !important;"></th>
                                    </tr>
                         </tfoot> -->
                        </table>




                        <script>
                            $(document).ready(function() {
                                $.fn.dataTable.ext.classes.sPageButton = 'btn-pagination btn-sm'; // Change Pagination Button Class


                                @php
                                    $department_id = request()->get('department_id');
                                    $parent_department_id = request()->get('parent_department_id');
                                    $sector_id = request()->get('sector_id'); // Get department_id from request
                                    $type = request()->get('type'); // Get department_id from request
                                    // dd($sector_id);
                                    //  $Dataurl = 'api/users';
                                    $Dataurl = 'api.users';
                                    if (isset($mode)) {
                                        if ($mode == 'search') {
                                            //  $Dataurl = 'searchUsers/users';
                                            $Dataurl = 'search.user';
                                        }
                                    }
                                    $parms = [];
                                    // dd($Dataurl);
                                    if ($department_id) {
                                        //  $Dataurl .= '?department_id=' . $department_id;
                                        /*  if ($parms != '') {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    $parms .= '&';
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                } else {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    $parms = '?';
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                } */
                                        $parms['department_id'] = $department_id;
                                    }
                                    if ($parent_department_id) {
                                        //  $Dataurl .= '?department_id=' . $department_id;
                                        /*  if ($parms != '') {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    $parms .= '&';
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                } else {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    $parms = '?';
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                } */
                                        $parms['parent_department_id'] = $parent_department_id;
                                    }

                                    if ($sector_id) {
                                        /*   if ($parms != '') {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    $parms .= '&';
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                } else {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    $parms = '?';
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                }*/

                                        $parms['sector_id'] = $sector_id;
                                    }
                                    if ($type) {
                                        /*  if ($parms != '') {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    $parms .= '&';
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                } else {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        $parms = '?';
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                }*/

                                        $parms['type'] = $type;
                                    }
                                    //dd($parms);
                                    //  $url_data = route($Dataurl, $parms);

                                    //  dd(http_build_query($parms));
                                    // $Dataurl .= '?' . http_build_query($parms);
                                    // dd($url_data);
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


                                            //  console.log("dalia", data);
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

                                        <a href="` + usershow + `"  class="btn btn-sm " style="background-color: #274373;"> <i class="fa fa-eye"></i>عرض  </a>
                                        <a href="` + useredit + `" class="btn btn-sm"  style="background-color: #F7AF15;"> <i class="fa fa-edit"></i> تعديل </a>
                                        {{-- <a href="${vacation}"  "   class="btn btn-sm" style=" background-color:#864824; "> <i class="fa-solid fa-mug-hot" ></i> </a> --}}

                                        <a href="${unsigned}" class="btn btn-sm ${visibility}" style="background-color: #28a39c;">
                                            <i class="fa-solid fa-user-minus"></i> الغاء التعيين
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
