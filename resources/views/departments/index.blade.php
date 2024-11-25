@extends('layout.main')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
<script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js" defer></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer>
</script>

@section('content')
@section('title')
    عرض
@endsection
<div class="row " dir="rtl">
    <div class="container  col-11" style="background-color:transparent;">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item "><a href="{{ route('home') }}">الرئيسيه</a></li>
                <li class="breadcrumb-item"><a href="{{ route('sectors.index', ['uuid' => $sectors->uuid]) }}">القطاعات</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page"> <a href="">
                        الادارات الرئيسية</a></li>
            </ol>
        </nav>
    </div>
</div>
<section>
    <div class="row">
        <div class="container welcome col-11">
            <div class="d-flex justify-content-between">
                <p> الادارات </p>
                <div class="form-group">
                    @php
                        $uuid = Request::segment(2);
                    @endphp
                    @if (Auth::user()->rule->id == 1 || Auth::user()->rule->id == 2 || Auth::user()->rule->id == 4)
                        <button type="button" class="wide-btn "
                            onclick="window.location.href='{{ route('department.create', $uuid) }}'"
                            style="    color: #0D992C;">
                            اضافة جديد 
                            <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">
                        </button>
                    @endif
                    @if (Auth::user()->hasPermission('create Postman'))
                        <!--   <button type="button" class="wide-btn mx-md-3 mx-1"
                        onclick="window.location.href='{{ route('postmans.create') }}'">
                        اضافة مندوب
                       <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">
                    </button> -->
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="container  col-11 mt-3 p-0  pt-5 pb-4">
            <div class="row" dir="rtl">



            </div>

            <div class="col-lg-12">
                <div class="bg-white ">
                    <div>
                        <table id="users-table"
                            class="display table table-responsive-sm table-bordered table-hover dataTable">
                            <thead>
                                <tr>
                                    <th>رقم التعريف</th>
                                    <th>الاسم</th>
                                    <th>مدير الادارة</th>
                                    <th>بيانات الدخول</th>
                                    {{-- <th>الاقسام</th> --}}
                                    <th>ميزانية البدل</th>
                                    <th>صلاحيه الحجز</th>
                                    <th>عدد الأدارات الفرعيه</th>
                                    <th>عدد القوة</th>
                                    <th> عدد القوة بالادارات الفرعية</th>
                                    <th>إجراء</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    $(document).ready(function() {
        $.fn.dataTable.ext.classes.sPageButton =
            'btn-pagination btn-sm'; // Change Pagination Button Class
        @php
            $Dataurl = url('api/department') . '/' . $uuid;

            // if (isset($mode)) {
            //     if ($mode == 'search') {
            //         $Dataurl = url('searchDept/departments') . '/' . $q;
            //     }
            // }
            // dd($Dataurl);

        @endphp
        console.log('Rasha', "{{ $Dataurl }}");
        $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ $Dataurl }}/',

            columns: [{
                    sWidth: '50px',
                    data: null,
                    name: 'order', orderable: false, searchable: false
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'manager_name',
                    name: 'manager_name'
                },
                {
                    data: 'login_info',
                    name: 'login_info'
                },
                {
                    data: 'reservation_allowance_amount',
                    name: 'reservation_allowance_amount'
                },
                {
                    data: 'reservation_allowance',
                    name: 'reservation_allowance'
                }, // Ensure 'manager' column exists
                {
                    data: 'subDepartment',
                    name: 'subDepartment',
                    render: function(data, type, row) {
                        return '<button class="btn btn-sm" style="background-color: #274373; color: white; padding-inline: 15px" onclick="showSubDepartments(\'' + row.uuid + '\')">' + data + '</button>';
                    }
                },
                {
                    data: 'num_managers',
                    name: 'num_managers',
                    render: function(data, type, row) {
                        return '<button class="btn btn-sm" style="background-color: #274373; color: white; padding-inline: 15px" onclick="showUsers(\'' + row.uuid + '\')">' + data + '</button>';
                    }
                },
                {
                    data: 'num_subdepartment_managers',
                    name: 'num_subdepartment_managers',
                    render: function(data, type, row) {
                        return '<button class="btn btn-sm" style="background-color: #274373; color: white; padding-inline: 15px" onclick="showSubUsers(\'' + row.uuid + '\')">' + data + '</button>';
                    }
                },
                {
                    data: 'action',
                    name: 'action',
                    sWidth: '100px',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [0, 'asc'],
            columnDefs: [{
                targets: -1,
                render: function(data, type, row) {
                    console.log(row);
                    var departmentEdit = '{{ route('departments.edit', ':uuid') }}';
                    departmentEdit = departmentEdit.replace(':uuid', row.uuid);
                    var subdepartment = '{{ route('sub_departments.create', ':uuid') }}';
                    subdepartment = subdepartment.replace(':uuid', row.uuid);
                    var departmentShow = '{{ route('departments.show', ':uuid') }}';
                    departmentShow = departmentShow.replace(':uuid', row.uuid);
                    /*  var addReservation =
                         '{{ route('reservation_allowances.search_employee_new', 'sector_id=:sector&departement_id=:id') }}';
                     addReservation = addReservation.replace(':id', row.id);
                     addReservation = addReservation.replace(':sector', row.sector_id); */

                    // Get the authenticated user's department ID from Blade
                    // var authDepartmentId = {{ Auth::user()->department_id }};

                    // Start building the buttons
                    var buttons = `
                    <a href="${subdepartment}" class="btn btn-sm" style="background-color: #274373;"> <i class="fa fa-plus"></i> ادارات فرعيه</a>
                    <a href="${departmentShow}" class="btn btn-sm" style="background-color: #274373;"> <i class="fa fa-eye"></i> عرض</a>
                    `;

                    // Only show the Edit button if the row's department ID matches the authenticated user's department ID and is not 1
                    // if (row.id === authDepartmentId) {
                    buttons += `<a href="${departmentEdit}" class="btn btn-sm" style="background-color: #F7AF15;"> <i class="fa fa-edit"></i> تعديل</a>`;

                    /*    <a href="${addReservation}" class="btn btn-sm" style="background-color: #274373;"> <i class="fa fa-edit"></i> اضافة بدل حجز</a> */
                    // }

                    return buttons; // Return the constructed buttons
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
            },
            createdRow: function(row, data, dataIndex) {
                $('td', row).eq(0).html(dataIndex + 1); // Automatic numbering in the first column
            }
        });

    });

    // function deleteDepartment(id) {
    //     console.log(id);
    //     if (confirm('هل أنت متأكد من حذف هذا القسم؟')) {
    //         $.ajax({
    //             url: '/departments/delete/' + id,
    //             type: 'get',

    //             success: function(response) {
    //                 // Handle success, e.g., refresh DataTable, show success message
    //                 $('#users-table').DataTable().ajax.reload();
    //                 alert('تم حذف القسم بنجاح');
    //             },
    //             error: function(xhr) {
    //                 console.log(xhr);
    //                 // Handle error, e.g., show error message
    //                 // alert('حدث خطأ أثناء حذف القسم');
    //             }
    //         });
    //     }
    // }

    function showSubDepartments(departmentId) {
        window.location.href = '/sub_departments/' + departmentId;
    }

    function showUsers(departmentId) {
        window.location.href = '/employees/employee?department_id=' + departmentId;
    }

    function showSubUsers(parentDepartmentId) {
        window.location.href = '/employees/employee?parent_department_id=' + parentDepartmentId;
    }
</script>

@endsection
