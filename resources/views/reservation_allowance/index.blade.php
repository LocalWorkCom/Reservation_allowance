<style>

.div-info {
    border-radius: 10px;
    padding: 20px;
    margin-top: 20px;
    width: 200px;
    height: 150px;
    background-color: #27437329;
}
.div-info-padding{
    padding: 3px 0;
    direction: initial;
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
            <div class="d-flex justify-content-between">
                    <div class="col-12">
                        <div class="row" style="direction: rtl">
                            <div class="col-6"><p> بدل الحجز</p></div>
                            <div class="col-6">{{-- @if (Auth::user()->hasPermission('create reservation_allowances')) --}}
                                <button type="button" class="btn-all" onclick="addForm()" style="color: #0D992C;">

                                    اضافة بدل حجز جديد  <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">
                                </button>
                                {{-- @endif --}}
                            </div>


                            <div class="col-12 div-info">
                                <div class="row">
                                    <div class="col-6 div-info-padding"><b>القطاع : امن عام</b></div>
                                    <div class="col-6 div-info-padding"><b>الادارة الرئيسية : مديرية امن حولى</b></div>
                                    <div class="col-6 div-info-padding"><b>الادارة الفرعية : مخفر النقرة</b></div>
                                    <div class="col-6 div-info-padding"><b>مبلغ بدل الحجز : 1200 دينار</b></div>
                                    <div class="col-6 div-info-padding"><b>اليوم : السبت</b></div>
                                    <div class="col-6 div-info-padding"><b>التاريخ : 27/9/2024</b></div>
                                    <div class="col-6 div-info-padding"><b>عدد العسكرين المحجوزين : 3</b></div>
                                </div>
                            </div>

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
                    <div>
                        <table id="users-table"
                            class="display table table-responsive-sm  table-bordered table-hover dataTable">
                            <thead>
                                <tr>
                                <th>الرتيب</th>
                                <th>الرتبة</th>
                                <th>الاسم</th>
                                <th>رقم الملف</th>
                                <th>بدل الحجز</th>
                                <th>اليومية</th>
                                <th style="width:150px;">العمليات</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

        </div>

    </div>






    {{-- this for add form --}}
    <div class="modal fade" id="addForm" tabindex="-1" aria-labelledby="representativeLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-center">
                    <div class="title d-flex flex-row align-items-center">
                        <h5 class="modal-title" id="lable"> أضافه بدل حجز جديد</h5>

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
                        <form class="edit-grade-form" id="add-form" action=" {{ route('reservation_allowances.store') }}" method="POST">
                            @csrf
                            <input type="hidden" id="modalType" value="add">

                            <div class="form-group">
                                <label for="typeadd"> رقم الهوية</label>
                                <select name="typeadd" id="typeadd" aria-placeholder="اختر رقم الهوية"
                                    class="form-control" required>
                                    <option value="" selected disabled>اختر رقم الهوية</option>
                                    
                                </select>
                                <span class="text-danger span-error" id="typeadd-error" dir="rtl"></span>

                            </div>

                            <div class="form-group">
                                <label for="value_all">صلاحية الحجز</label>
                                حجز كلى
                                <input type="radio" id="value_all" name="type" class="form-control" checked value="1" required>
                                حجز جزئى
                                <input type="radio" id="value_all" name="type" class="form-control" value="2" required>
                                <span class="text-danger span-error" id="type-error" dir="rtl"></span>

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
    <?php /*
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
                                    <option value="0" {{ session('old_typeedit') == '0' ? 'selected' : '' }}>ظابط
                                    </option>
                                    <option value="1" {{ session('old_typeedit') == '1' ? 'selected' : '' }}>صف ظابط
                                    </option>
                                    <option value="2" {{ session('old_typeedit') == '2' ? 'selected' : '' }}> فرد
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
    */?>
@endsection
@push('scripts')
<script>
        $(document).ready(function() {
            function closeModal() {
                $('#delete').modal('hide');
            }

            $('#closeButton').on('click', function() {
                closeModal();
            });
        });
    </script>
    <script>
        function opendelete(id) {
            document.getElementById('id').value = id;
            $('#delete').modal('show');
        }

        function confirmDelete() {
            var id = document.getElementById('id').value;
            var form = document.getElementById('delete-form');
            form.submit();
        }
        $(document).ready(function() {
            // Check if there are errors
            @if ($errors->any())
                // Check if it's an add or edit operation
                @if (session('modal_type') === 'add')
                    $('#addForm').modal('show');
                @elseif (session('modal_type') === 'edit')
                    $('#edit').modal('show');
                @endif
            @endif
        });

        function openedit(id, name, type, value_all, value_part, order) {
            document.getElementById('nameedit').value = name;
            document.getElementById('idedit').value = id;
            document.getElementById('typeedit').value = type; // Set the value for type
            document.getElementById('value_alledit').value = value_all; // Set value_all
            document.getElementById('value_partedit').value = value_part; // Set value_part
            document.getElementById('orderedit').value = order; // Set value_part

            $('#edit').modal('show');
        }

        function confirmEdit() {
            var id = document.getElementById('id').value;
            var name = document.getElementById('nameedit').value;
            console.log(name);
            var form = document.getElementById('edit-form')
        }

        function addForm() {
            $('#addForm').modal('show');
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
                    url: '{{ route('reservation_allowances.getAll') }}',
                    data: function(d) {
                d.filter = filter; // Use the global filter variable
            }
                },
                columns: [
                    {
                        data: 'id',
                        name: 'id'
                    }, 
                    {
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
                    console.log('Page ' + this.api().page.info().pages)
                    var page = this.api().page.info().pages;
                    console.log($('#users-table tr').length);
                    if (page == 1) {
                        //   $('.dataTables_paginate').hide();//css('visiblity','hidden');
                        $('.dataTables_paginate').css('visibility', 'hidden'); // to hide

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
