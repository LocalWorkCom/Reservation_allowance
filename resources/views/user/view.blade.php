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
                    <p>موظفين القوة<< /p>
                @endif
                <div class="form-group">
                    @if (Auth::user()->hasPermission('add_employee User'))
                        <button type="button" class="wide-btn"
                            onclick="window.location.href='{{ route('user.create') }}'" style="color: #0D992C;">
                            اضافة جديد <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">
                        </button>
                        {{-- <a href="{{ route('export-users') }}" class="btn btn-primary"
                            style="border-radius: 5px;">تصدير</a> --}}
                        <a href="{{ route('download-template') }}" class="btn "
                            style="border-radius: 5px;background-color: #274373; border-color: #274373; color:white; border-radius:10px;">تحميل القالب</a>
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
            
            <form action="{{ route('import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="d-flex justify-content-between mt-4" dir="rtl">
                <div class="d-flex form-group">
                    <label for="file" style="color: #555;    font-weight: 700;
    line-height: 48.78px;">اختر الملف المراد استيراده :</label>
                    <input type="file" name="file" class="form-control mb-2" accept=".csv, .xlsx"
                        style="    border: 1px solid #27437324;  border-radius: 5px;background-color: transparent;width: 220px;">
                </div>
                <div class="d-flex">
                    <button type="submit" class="btn mx-2 px-4"style="border-radius: 5px;background-color: #274373; border-color: #274373; color:white; border-radius:10px; height:40px;">استيراد</button>
                </div>  </div>
            </form>
          
            @include('inc.flash')
            <div class="col-lg-12 pt-5 pb-5">
                <div class="bg-white ">

                    <div>
                        <table id="users-table"
                            class="display table table-responsive-sm  table-bordered table-hover dataTable">
                            <thead>
                                <tr>
                                    <th>رقم التعريف</th>
                                    <th>الاسم</th>
                                    <th>القسم</th>
                                    <th>الهاتف</th>
                                    <th>الرقم العسكري</th>
                                    <th>النوع</th>
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
                                    $Dataurl = url('api/users');
                                    if (isset($mode)) {
                                        if ($mode == 'search') {
                                            $Dataurl = url('searchUsers/users');
                                        }
                                    }
                                    // dd($Dataurl);
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
                                    ajax: '{{ $Dataurl }}/' +
                                        '{{ isset($q) ? $q : '' }}', // Correct URL concatenation
                                    bAutoWidth: false,
                                    columns: [{
                                            data: 'id',
                                            sWidth: '50px',
                                            name: 'id'
                                        },
                                        {
                                            data: 'name',
                                            name: 'name'
                                        },
                                        {
                                            data: 'department',
                                            name: 'department'
                                        },
                                        {
                                            data: 'phone',
                                            name: 'phone'
                                        },
                                        {
                                            data: 'military_number',
                                            name: 'military_number'
                                        },
                                        {
                                            data: 'flag',
                                            name: 'flag'
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
                                        console.log('Page ' + this.api().page.info().pages)
                                        var page = this.api().page.info().pages;
                                        console.log($('#users-table tr').length);
                                        if (page == 1) {
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
@endsection
