@extends('layout.main')
@push('style')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js" defer></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer>
    </script>
@endpush

@section('title')
    الرتـــــــب
@endsection
@section('content')
    <section>
        <div class="row">
            <div class="container welcome col-11">
                <div class="d-flex justify-content-between">
                    <p> الرتـــــــب</p>
                    @if (Auth::user()->hasPermission('edit grade'))
                        <button type="button" class="btn-all  " onclick="openadd()" style="    color: #0D992C;">

                            اضافة رتبة جديده <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <br>
        <div class="row">
            <div class="container  col-11 mt-3 pb-4 p-0 ">
                <div class="row d-flex justify-content-between " dir="rtl">
                    <div class="form-group moftsh mt-4  mx-4  d-flex">
                        <p class="filter "> تصفية حسب:</p>
                        <button class="btn-all px-3 mx-2 btn-filter btn-active" data-filter="all" style="color: #274373;">
                            الكل ({{ $all }})
                        </button>
                        <button class="btn-all px-3 mx-2 btn-filter" data-filter="assigned" style="color: #274373;">
                            رتب الضباط ({{ $Officer }})
                        </button>
                        <button class="btn-all px-3 mx-2 btn-filter" data-filter="unassigned" style="color: #274373;">
                            رتب الأفراد و المهنيين ({{ $Officer2 + $person }})
                        </button>
                    </div>
                    {{-- <div class="form-group mt-4 mx-4  d-flex justify-content-end ">
                        <button class="btn-all px-3 " style="color: #FFFFFF; background-color: #274373;"
                            onclick="window.print()">
                            <img src="{{ asset('frontend/images/print.svg') }}" alt=""> طباعة
                        </button>
                    </div> --}}
                </div>

                <div class="container  col-11 mt-3 p-0  pt-5 pb-4">


                    <div class="col-lg-12">
                        <div class="bg-white ">
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
                                            <th>الترتيب</th>
                                            <th>الرتبه</th>
                                            <th>الفئة</th>
                                            <th>بدل حجز كلى</th>
                                            <th>بدل حجز جزئى</th>
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


    {{-- this for add form --}}
    <div class="modal fade" id="add" tabindex="-1" aria-labelledby="representativeLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-center">
                    <div class="title d-flex flex-row align-items-center">
                        <h5 class="modal-title" id="lable"> أضافه رتبه جديد</h5>

                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> &times;
                    </button>
                </div>
                <div class="modal-body  mt-3 mb-5 ">

                    <div class="container pt-5 pb-3" style="border: 0.2px solid rgb(166, 165, 165);">
                        @if ($errors->any())
                            <div class="alert alert-danger"dir="rtl">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form class="edit-grade-form" id="add-form" action=" {{ route('grads.add') }}" method="POST">
                            @csrf
                            <input type="hidden" id="modalType" value="add">

                            <div class="form-group">
                                <label for="name">اسم الرتبة</label>
                                <input type="text" id="nameadd" name="nameadd" class="form-control"
                                    placeholder="أدخل أسم الرتبه" required>
                                <span class="text-danger span-error" id="nameadd-error" dir="rtl"></span>

                            </div>
                            <div class="form-group">
                                <label for="typeadd"> الفئة</label>
                                <select name="typeadd" id="typeadd" aria-placeholder="اختر نوع الرتبه"
                                    class="form-control" required>
                                    <option value="" selected disabled>اختر نوع الرتبه</option>
                                    <option value="2">ظابط</option>
                                    <option value="1">فرد </option>
                                    <option value="3"> مهني</option>
                                </select>
                                <span class="text-danger span-error" id="typeadd-error" dir="rtl"></span>

                            </div>
                            <div class="form-group">
                                <label for="order">الترتيب</label>
                                <input type="number" id="order" name="order" class="form-control" required>
                                <span class="text-danger span-error" id="order-error" dir="rtl"></span>

                            </div>
                            <div class="form-group">
                                <label for="value_all">بدل حجز كلى</label>
                                <input type="text" id="value_all" name="value_all" class="form-control"
                                    placeholder="أدخل بدل الحجز الكلى" required>
                                <span class="text-danger span-error" id="value_all-error" dir="rtl"></span>

                            </div>
                            <div class="form-group">
                                <label for="value_part">بدل حجز جزئى</label>
                                <input type="text" id="value_part" name="value_part" class="form-control"
                                    placeholder="أدخل مبلغ بدل الحجز الجزئى" required>
                                <span class="text-danger span-error" id="value_part-error" dir="rtl"></span>

                            </div>

                            <!-- Save button -->
                            <div class="text-end">
                                <button type="submit" class="btn-blue" onclick="confirmAdd()">اضافه</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- this for edit form --}}
    <div class="modal fade" id="edit" tabindex="-1" aria-labelledby="representativeLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-center">
                    <div class="title d-flex flex-row align-items-center">
                        <h5 class="modal-title" id="label">تعديل اسم الرتبه ؟</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        &times;</button>
                </div>
                <div class="modal-body mt-3 mb-5">
                    <div class="container pt-5 pb-3" style="border: 0.2px solid rgb(166, 165, 165);">
                        @if ($errors->any())
                            <div class="alert alert-danger" dir="rtl">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form class="edit-grade-form" id="edit-form" action="{{ route('grads.update') }}"
                            method="POST">
                            @csrf

                            <input type="hidden" id="modalTypeEdit" value="edit">

                            <div class="form-group ">
                                <label for="name">أسم الرتبة</label>
                                <input type="text" id="nameedit" name="name" class="form-control" dir="rtl"
                                    value="{{ session('old_name') }}" required>
                                <input type="hidden" id="idedit" name="id" value="{{ session('edit_id') }}">
                            </div>
                            <div class="form-group">
                                <label for="typeedit">الفئة</label>
                                <select name="typeedit" id="typeedit" class="form-control">
                                    <option value="" selected disabled>اختر نوع الرتبه</option>
                                    <option value="2" {{ session('old_typeedit') == '2' ? 'selected' : '' }}>ظابط
                                    </option>
                                    <option value="1" {{ session('old_typeedit') == '1' ? 'selected' : '' }}> فرد
                                    </option>
                                    <option value="3" {{ session('old_typeedit') == '3' ? 'selected' : '' }}> مهني
                                    </option>
                                </select>
                                <span class="text-danger span-error" id="typeedit-error" dir="rtl"></span>
                            </div>
                            <div class="form-group">
                                <label for="name">الترتيب</label>
                                <input type="number" id="orderedit" name="orderedit"
                                    value="{{ session('old_orderedit') }}" class="form-control" required>
                                <span class="text-danger span-error" id="orderedit-error" dir="rtl"></span>

                            </div>
                            <div class="form-group">
                                <label for="value_alledit">بدل حجز كلى</label>
                                <input type="text" id="value_alledit" name="value_alledit" class="form-control"
                                    value="{{ session('old_value_alledit') }}" required>
                                <span class="text-danger span-error" id="value_all-error" dir="rtl"></span>
                            </div>
                            <div class="form-group">
                                <label for="value_partedit">بدل حجز جزئى</label>
                                <input type="text" id="value_partedit" name="value_partedit" class="form-control"
                                    value="{{ session('old_value_partedit') }}" required>
                                <span class="text-danger span-error" id="value_partedit-error" dir="rtl"></span>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn-blue">تعديل</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- model for delete form --}}
    <div class="modal fade" id="delete" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-center">
                    <div class="title d-flex flex-row align-items-center">
                        <h5 class="modal-title" id="deleteModalLabel"> !تنبــــــيه</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> &times;
                    </button>
                </div>
                <div class="modal-body  mt-3 mb-5">
                    <div class="container pt-5 pb-3" style="border: 0.2px solid rgb(166, 165, 165);">
                        <form id="delete-form" action="{{ route('grads.delete') }}" method="POST">
                            @csrf
                            <div class="form-group d-flex justify-content-center ">
                                <h5 class="modal-title " id="deleteModalLabel"> هل تريد حذف هذه الرتبه ؟</h5>


                                <input type="text" id="id" hidden name="id" class="form-control"
                                    dir="rtl">
                            </div>
                            <!-- Save button -->
                            <div class="text-end">
                                <div class="modal-footer mx-2 d-flex justify-content-center">
                                    <div class="text-end">
                                        <button type="button" class="btn-blue" id="closeButton">لا</button>
                                    </div>
                                    <div class="text-end">
                                        <button type="submit" class="btn-blue" onclick="confirmDelete()">نعم</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            // Check if there are errors
            @if ($errors->any())
                // Check if it's an add or edit operation
                @if (session('modal_type') === 'add')
                    $('#add').modal('show');
                @elseif (session('modal_type') === 'edit')
                    $('#edit').modal('show');
                @endif
            @endif
        });
        $(document).ready(function() {
            function closeModal() {
                $('#delete').modal('hide');
            }

            $('#closeButton').on('click', function() {
                closeModal();
            });
        });

        function confirmDelete() {
            var id = document.getElementById('id').value;
            var form = document.getElementById('delete-form');
            form.submit();
        }

        function opendelete(id) {
            document.getElementById('id').value = id;
            $('#delete').modal('show');
        }
    </script>
    <script>
        function handleAction(action, id, name, type, value_all, value_part, order) {
            alert(action, id, name, type, value_all, value_part, order)
            if (action === "edit") {
                openedit(id, name, type, value_all, value_part, order);
            } else if (action === "delete") {
                opendelete(id);
            }
        }

        function openedit(id, name, type, value_all, value_part, order) {
            // Set the modal fields
            document.getElementById('nameedit').value = name || '';
            document.getElementById('idedit').value = id || '';
            document.getElementById('typeedit').value = type || ''; // Type
            document.getElementById('value_alledit').value = value_all || ''; // Value All
            document.getElementById('value_partedit').value = value_part || ''; // Value Part
            document.getElementById('orderedit').value = order || ''; // Order

            // Open the modal
            $('#edit').modal('show');
        }


        function confirmEdit() {
            var id = document.getElementById('id').value;
            var name = document.getElementById('nameedit').value;
            var form = document.getElementById('edit-form')
        }

        function openadd() {
            $('#add').modal('show');
        }



        function confirmAdd() {
            var name = document.getElementById('nameadd').value;
            var idedit = document.getElementById('idedit').value;
            var value_part = document.getElementById('value_part').value;
            var value_all = document.getElementById('value_all').value;
            var order = document.getElementById('order').value;

            var form = document.getElementById('add-form');
            var inputs = form.querySelectorAll('[required]');
            var valid = true;

            inputs.forEach(function(input) {
                if (!input.value) {
                    valid = false;
                    input.style.borderColor = 'red'; // Optional: highlight empty inputs
                } else {
                    input.style.borderColor = ''; // Reset border color if input is filled
                }
            });

            if (valid) {
                form.submit();
            }
        }

        $(document).ready(function() {
            $.fn.dataTable.ext.classes.sPageButton = 'btn-pagination btn-sm'; // Change Pagination Button Class

            var filter = 'all'; // Default filter

            const table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('setting.getAllgrads') }}',
                    data: function(d) {
                        d.filter = filter; // Use the global filter variable
                    }
                },
                // ajax: '{{ route('setting.getAllgrads') }}', // Correct URL concatenation
                columns: [

                    {
                        data: 'order',
                        name: 'order'
                    }, {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },
                    {
                        data: 'value_all',
                        name: 'value_all'
                    },
                    {
                        data: 'value_part',
                        name: 'value_part'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        sWidth: '100px',
                        orderable: false,
                        searchable: false
                    }
                ],
                "order": [0, 'asc'],

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
@endpush
