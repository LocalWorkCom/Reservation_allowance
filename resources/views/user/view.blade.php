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
                                <input type="text" name="id_employee" id="id_employee" value="">
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
                @php
                    // Get the department based on department_id from the request
                    $department = \App\Models\departements::where('uuid',request()->get('department_id'))->first();
                @endphp

                @if (request()->fullUrlIs('*employees/employee?department_id=*'))
                    {{-- Check if department and sector are available --}}
                    <p>قوة/
                        {{ $department->sectors->name }}/
                        {{ $department->name }} <!-- Display department name -->
                    </p>
                @elseif (Auth::user()->rule_id != 2)
                    @if ($flag == 'employee')
                        <p>موظفين القوة</p>
                    @else
                        <p>المستخدمين والصلاحيات</p>
                    @endif
                @elseif (Auth::user()->rule_id == 2)
                    @if ($flag == 'employee')
                        <p>موظفين الوزارة</p>
                    @else
                        <p>المستخدمين والصلاحيات</p>
                    @endif
                @endif

                <div class="form-group">

                    @if (Auth::user()->hasPermission('add_employee User'))
                        @if ($flag == 'employee')
                            <a href="{{ route('download-template') }}" class="btn-all text-info mx-2 p-2">تحميل
                                القالب</a>

                            <button type="button" class="wide-btn mx-2"
                                onclick="window.location.href='{{ route('user.create') }}'" style="color: #0D992C;">
                                اضافة موظف جديد <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">
                            </button>
                        @endif

                </div>
                @if (request()->fullUrlIs('*employees/employee?department_id=*'))
                    <form class="" action="{{ route('user.employees.add') }}" method="post">
                        <input type="hidden" id="department_id" name="department_id"
                            value="{{ request()->get('department_id') }}" class="form-control">
                        @csrf
                        <div class="row d-flex flex-wrap ">
                            <!-- 1 for sector , 2 for department -->
                            <div class="d-flex">
                                <input type="text" id="Civil_number" name="Civil_number" class="form-control"
                                    placeholder="الرقم المدني" style="border-radius:10px !important;">
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
                <div class="row d-flex justify-content-between " dir="rtl">
                    <div class="form-group moftsh mt-4  mx-4  d-flex">
                        <p class="filter "> تصفية حسب :</p>
                        <button class="btn-all px-3 mx-2 btn-filter btn-active" data-filter="all"
                            style="color: #274373;">
                            الكل ({{ $all }})
                        </button>
                        <button class="btn-all px-3 mx-2 btn-filter" data-filter="Officer" style="color: #274373;">
                            رتب الضباط ({{ $Officer }})
                        </button>
                        <button class="btn-all px-3 mx-2 btn-filter" data-filter="Officer2" style="color: #274373;">
                            رتب المهنيين ({{ $Officer2 }})
                        </button>

                        <button class="btn-all px-3 mx-2 btn-filter" data-filter="person" style="color: #274373;">
                            رتب الأفراد ({{ $person }})
                        </button>
                    </div>
                </div>
                <table id="users-table" class="display table table-responsive-sm  table-bordered table-hover dataTable">
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
                        var filter = 'all'; // Default filter

                        @php
                            // $department_id = request()->get('department_id');
                            // $parent_department_id = request()->get('parent_department_id');
                            // $sector_id = request()->get('sector_id');
                            // $type = request()->get('type');
                            // $Civil_number = request()->get('Civil_number'); // Get Civil_number from request
                            $Dataurl = 'api.users'; // Default URL

                            if (isset($mode) && $mode == 'search') {
                                $Dataurl = 'search.user';
                            }

                            $parms['flag'] = $flag;
                            $parms['id'] = $id;
                            $parms['type'] = $type;
                            // if ($department_id) {
                            //     $parms['id'] = $department_id;
                            // $parms['type'] = 'department';

                            // }
                            // if ($parent_department_id) {
                            //     $parms['id'] = $parent_department_id;
                            // $parms['type'] = 'parent';

                            // }
                            // if ($sector_id) {
                            //     $parms['id'] = $sector_id;
                            // $parms['type'] = 'sector';

                            // }
                            // if ($Civil_number) {
                            //     $parms['Civil_number'] = $Civil_number; // Add Civil_number to parameters
                            // }
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
                          var url = '{{ route($Dataurl) }}';
var query = '{{ isset($q) ? '/' . $q : '' }}';

// Manually append the parameters
var fullUrl = url + query + '?flag=employee&id={{ urlencode($parms['id']) }}&type={{ urlencode($parms['type']) }}';

                        var table = $('#users-table').DataTable({
                            processing: true,
                            serverSide: true,
                            ajax: {
                                url: fullUrl,
                                data: function(d) {
                                    d.filter = filter;
                                    // d.department_id = department_id; // Use the global filter variable
                                    // Use the global filter variable
                                }
                            },
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
                                    var useredit = '{{ route('user.edit', ':uuid') }}';
                                    useredit = useredit.replace(':uuid', row.uuid);
                                    var usershow = '{{ route('user.show', ':uuid') }}';
                                    usershow = usershow.replace(':uuid', row.uuid);
                                    var vacation = '';
                                    var unsigned = '{{ route('user.unsigned', ':uuid') }}';
                                    unsigned = unsigned.replace(':uuid', row.uuid);
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
