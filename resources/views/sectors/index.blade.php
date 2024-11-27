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

        <div class="container welcome col-11">
            <div class="d-flex justify-content-between">
                <p> القطاعـــات</p>
                @if (Auth::user()->rule->id == 1 || Auth::user()->rule->id == 2)
                    <button type="button" class="btn-all  " onclick="window.location.href='{{ route('sectors.create') }}'"
                        style="color: #0D992C;">

                        اضافة قطاع جديد <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">
                    </button>
                @endif
            </div>
        </div>
    </div>

    <br>
    <div class="row">
        <div class="container  col-11 mt-3 p-0  pt-5 pb-4">
            <div class="row " dir="rtl">

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
                            class="display table table-responsive-sm  table-bordered table-hover dataTable">
                            <thead>
                                <tr>
                                    <th>الاسم</th>
                                    <th>مدير القطاع</th>
                                    <th>بيانات المستخدم</th>
                                    <th>عدد الأدارات التابعه </th>
                                    <th>ميزانية البدل</th>
                                    <th>صلاحيه الحجز</th>
                                    <th>عدد القوة</th>
                                    <th>عدد قوة الأدارات</th>
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
            $.fn.dataTable.ext.classes.sPageButton =
                'btn-pagination btn-sm';
            var table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('getAllsectors') }}',
                },
                columns: [{
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
                        data: 'departments',
                        name: 'departments'
                    },

                    {
                        data: 'reservation_allowance_amount',
                        name: 'reservation_allowance_amount'
                    },
                    {
                        data: 'reservation_allowance',
                        name: 'reservation_allowance'
                    },
                    {
                        data: 'employees',
                        name: 'employees'
                    },
                    {
                        data: 'employeesdep',
                        name: 'employeesdep'
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
                        "sFirst": '<i class="fa fa-fast-backward" aria-hidden="true"></i>',
                        "sPrevious": '<i class="fa fa-chevron-left" aria-hidden="true"></i>',
                        "sNext": '<i class="fa fa-chevron-right" aria-hidden="true"></i>',
                        "sLast": '<i class="fa fa-step-forward" aria-hidden="true"></i>'
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
        });

        function handleAction(action, uuid) {
            switch (action) {
                case "show":
                    // Redirect to the "show" page
                    var showUrl = '{{ route('sectors.show', ':uuid') }}'.replace(':uuid', uuid);
                    window.location.href = showUrl;
                    break;
                case "edit":
                    // Redirect to the "edit" page
                    var editUrl = '{{ route('sectors.edit', ':uuid') }}'.replace(':uuid', uuid);
                    window.location.href = editUrl;
                    break;
                case "create-department":
                    // Redirect to the "create department" page
                    var createDeptUrl = '{{ route('department.create', ':uuid') }}'.replace(':uuid', uuid);
                    window.location.href = createDeptUrl;
                    break;
                default:
                    console.error("Invalid action selected: " + action);
            }
        }
    </script>
@endpush
