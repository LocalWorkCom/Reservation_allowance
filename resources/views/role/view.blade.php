@extends('layout.main')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
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
                <p>المـــــــهام</p>
                @if (Auth::user()->hasPermission('create Rule'))
                    <button type="button" class="btn-all" onclick="window.location.href='{{ route('rule.create') }}'"
                        >
                        اضافة جديد 
                    </button>
                @endif
            </div>
        </div>
    </div>


    <div class="row">
        <div class="container  col-11 mt-3 p-0 ">
            <div class="row " dir="rtl">
                <div class="form-group mt-4  mx-2 col-12 d-flex ">

                </div>
            </div>
            <div class="col-lg-12">
                <div class="bg-white">
                    <div>
                        <table id="users-table"
                            class="display table table-responsive-sm  table-bordered table-hover dataTable">
                            <thead>
                                <tr>
                                    <th>م</th>
                                    <th>الاسم</th>
                                    <th>الصلاحيات</th>
                                    <th>القسم</th>
                                    <th style="width:150px;">العمليات</th>
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
        $.fn.dataTable.ext.classes.sPageButton = 'btn-pagination btn-sm'; // Change Pagination Button Class

        $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ url('api/rule') }}',
            columns: [{
                    data: 'id',
                    sWidth: '50px',
                    name: 'id'
                },
                {
                    data: 'name',
                    sWidth: '60px',
                    name: 'name'
                },
                {
                    data: 'permissions',
                    sWidth: '400px',
                    name: 'permissions'
                },
                {
                    data: 'department',
                    sWidth: '60px',
                    name: 'department'
                },
                {
                    data: 'action',
                    name: 'action',
                    sWidth: '50px',
                    orderable: false,
                    searchable: false
                }
            ],
            columnDefs: [{
                targets: -1,
                render: function(data, type, row) {
                    // Using route generation correctly in JavaScript
                    var ruleedit = '{{ route('rule_edit', ':id') }}'.replace(':id', row.id);
                    var ruleshow = '{{ route('rule_show', ':id') }}'.replace(':id', row.id);
                    var canEdit = `<?php echo Auth::user()->hasPermission('edit Rule') ? 'd-block-inline' : 'd-none'; ?>`;
                    var canShow = `<?php echo Auth::user()->hasPermission('view Rule') ? 'd-block-inline' : 'd-none'; ?>`;

                    return `
            <select class="form-select form-select-sm btn-action" onchange="handleAction(this.value, '${row.id}', '${ruleshow}', '${ruleedit}')" aria-label="Actions" style="width: auto;">
                <option value="" class="text-center" style="color: gray;" selected disabled>الخيارات</option>
                <option value="show" class="text-center ${canShow}" data-url="${ruleshow}" style="color: #274373;">عرض</option>
                <option value="edit" class="text-center ${canEdit}" data-url="${ruleedit}" style="color:#eb9526;">تعديل</option>
            </select>
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
    });

    function handleAction(action, id, showUrl, editUrl) {
        if (action === 'show') {
            window.location.href = showUrl; // Redirect to the "show" page
        } else if (action === 'edit') {
            window.location.href = editUrl; // Redirect to the "edit" page
        } else {
            console.log('No action selected');
        }
    }
</script>
@endsection
