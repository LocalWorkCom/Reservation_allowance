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
    احصائيات القطاعات
@endsection

@section('content')
<div class="row">
    <div class="container welcome col-11">
        <div class="d-flex justify-content-between">
            <p>احصائيات القطاعات</p>
        </div>
    </div>
</div>

<br>

<div class="row" dir="rtl">
    <div class="container col-11  pt-5 pb-4">
    <div class="row ">
        
  
        <div class="col-12">
        
                    @if (session()->has('message'))
                        <div class="alert alert-info">
                            {{ session('message') }}
                        </div>
                    @endif
                
                <!-- Month and Year Selection Form -->
             
              <form id="filter-form" class="pb-3">
                 <div class="d-flex justify-content-between">
                   <div class="d-flex">
                   <!-- <label for="month-select" class="me-2">الشهر : </label> -->
                    <select id="month-select" name="month" class="btn-all mx-2">
                       <option selected disabled>
                        الشهر
                            </option> 
                             @for ($m = 1; $m <= 12; $m++)
                       
                            <option value="{{ $m }}" {{ $m == now()->month ?  : '' }}>
                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                            </option>
                        @endfor
                    </select>

                    <!-- <label for="year-select" class="me-2">السنة :</label> -->
                    <select id="year-select" name="year" class="btn-all  mx-2">
                    <option selected disabled>
                    السنة
                            </option> 
                        @for ($y = 2020; $y <= date('Y'); $y++)
                            <option value="{{ $y }}" {{ $y == now()->year ?  : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                   </div>

                    <button type="submit" class="btn-all text-info">عرض الإحصائيات</button>
               </div> </form>
              

                <div>
                    <table id="users-table" class="display table table-responsive-sm table-bordered table-hover dataTable">
                        <thead>
                            <tr>
                                <th>الترتيب</th>
                                <th>القطاع</th>
                                <th> الادارات الرئيسيه</th>
                                <th> الادارات الفرعيه</th>
                                <th>ميزانيه بدل حجز</th>
                                <th>المسجل</th>
                                <th>المتبقى</th>
                                <th>عدد الموظفين</th>
                                <th>حاصل  بدل حجز</th>
                                <th>لم يحصل  بدل حجز</th>
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
             $.fn.dataTable.ext.classes.sPageButton =
            'btn-pagination btn-sm';
           
            var table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                deferLoading: 0, 
                ajax: {
                    url: '{{ route('Reserv_statistic_sector.getAll') }}',
                    data: function(d) {
                        d.month = $('#month-select').val();
                        d.year = $('#year-select').val();
                    }
                },
                columns: [
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row, meta) {
                            return meta.row + 1; // Auto-generate row numbers
                        }
                    },

                    { data: 'sector', name: 'sector',
                        render: function (data, type, row) {
                            const month = $('#month-select').val();
                            const year = $('#year-select').val();
                            return `<a href="/sector-employees/${row.uuid}?month=${month}&year=${year}" style="color:#17a2b8 !important;">${data}</a>`;
                        },
                    },
                    { 
                    data: 'main_departments_count', 
                    name: 'main_departments_count', 
                    render: function(data, type, row) {
                        const month = $('#month-select').val(); 
                        const year = $('#year-select').val();   
                        return `<a href="/statistics_department/${row.id}?month=${month}&year=${year}" style="color:#17a2b8 !important;">${data}</a>`;
                    }
                },
                    { data: 'sub_departments_count', name: 'sub_departments_count',
                        render: function(data, type, row) {
                        const month = $('#month-select').val(); 
                        const year = $('#year-select').val();   
                        return `<a href="/statistics_department/${row.id}?month=${month}&year=${year}" style="color:#17a2b8 !important;">${data}</a>`;
                    }
                     },
                    { data: 'reservation_allowance_budget', name: 'reservation_allowance_budget' },
                    { data: 'registered_amount', name: 'registered_amount',
                        render: function (data, type, row) {
                            const month = $('#month-select').val();
                            const year = $('#year-select').val();
                            return `<a href="/sector-employees/${row.id}?month=${month}&year=${year}" style="color:#17a2b8 !important;">${data}</a>`;
                        },
                     },
                    { data: 'remaining_amount', name: 'remaining_amount' },
                    {
                        data: 'employees_count',
                        name: 'employees_count',
                        render: function(data, type, row) {
                            const month = $('#month-select').val();
                            const year = $('#year-select').val();
                            return `<a href="/sector-users/${row.id}?month=${month}&year=${year}" style="color:#17a2b8 !important;">${data}</a>`;
                        }
                    },
                    { 
                        data: 'received_allowance_count', 
                        name: 'received_allowance_count', 
                        render: function (data, type, row) {
                            const month = $('#month-select').val();
                            const year = $('#year-select').val();
                            return `<a href="/sector-employees/${row.id}?month=${month}&year=${year}" style="color:#17a2b8 !important;">${data}</a>`;
                        },
                    },

                    {
                        data: 'did_not_receive_allowance_count',
                        name: 'did_not_receive_allowance_count',
                        render: function (data, type, row) {
                            const month = $('#month-select').val();
                            const year = $('#year-select').val();
                            return `<a href="/sector-employees/${row.id}/not-reserved?month=${month}&year=${year}" style="color:#17a2b8 !important;">${data}</a>`;
                        }
                    },


                ],
                
                order: [
                    [2, 'desc']
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

            $('#filter-form').on('submit', function(e) {
                e.preventDefault();
                table.ajax.reload();
            });
        });
    </script>
@endpush
