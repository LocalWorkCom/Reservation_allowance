@extends('layout.main')
@push('style')
@endpush

@section('title')
    الدول و الجنسيات
@endsection
@section('content')
    <section>
        <div class="row">

            <div class="container welcome col-11">
                <div class="d-flex justify-content-between">
                    <p> الدول والجنسيات </p>
                    @if (Auth::user()->hasPermission('edit job'))
                        <button type="button" class="btn-all  " onclick="openadd()">

                            اضافة دولة جديدة
                        </button>
                    @endif
                </div>
            </div>
        </div>
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
                                        <th>الاسم</th>
                                        <th>الكود</th>
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
    <div class="modal fade show" id="add" tabindex="-1" aria-labelledby="representativeLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-center">
                    <div class="title d-flex flex-row align-items-center">
                        <h5 class="modal-title" id="lable"> أضافه دولة جديدة</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeModalBtn"
                        aria-label="Close">&times;</button>
                </div>
                <div class="modal-body mt-3 mb-5">
                    <div class="container pt-5 pb-4" style="border: 0.2px solid rgb(166, 165, 165);">
                        <form class="edit-grade-form" id="add-form" action="{{ route('nationality.add') }}" method="POST">
                            @csrf
                            <!-- Name Field -->
                            <div class="form-group">
                                <label for="name">الاسم</label>
                                <input type="text" id="nameadd" name="nameadd" class="form-control"
                                    value="{{ old('nameadd') }}" required>
                                @error('nameadd')
                                    <span class="text-danger" dir="rtl">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Code Field -->
                            <div class="form-group">
                                <label for="name">الكود</label>
                                <input type="text" id="codeAdd" name="codeAdd" class="form-control"
                                    value="{{ old('codeAdd') }}">
                                @error('codeAdd')
                                    <span class="text-danger" dir="rtl">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Save Button -->
                            <div class="text-end">
                                <button type="submit" class="btn-blue">اضافه</button>
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
                        <h5 class="modal-title" id="lable"> تعديل على مسمى الدولة ؟</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeModalBtn1" aria-label="Close">
                        &times; </button>
                </div>
                <div class="modal-body mt-3 mb-5">
                    <div class="container pt-5 pb-4" style="border: 0.2px solid rgb(166, 165, 165);">
                        <form class="edit-grade-form" id="edit-form" action="{{ route('nationality.update') }}"
                            method="POST">
                            @csrf

                            <div class="form-group">
                                <label for="name">الاسم</label>
                                <input type="text" id="nameedit" name="name" class="form-control"
                                    value="{{ old('name') ?? session('old_name') }}" required>
                                @error('name')
                                    <span class="text-danger" dir="rtl">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="codeedit">الكود</label>
                                <input type="text" id="codeedit" name="codeedit" class="form-control"
                                    value="{{ old('codeedit') ?? session('old_codeedit') }}">
                                @error('codeedit')
                                    <span class="text-danger" dir="rtl">{{ $message }}</span>
                                @enderror
                            </div>

                            <input type="hidden" name="id" id="idedit" value="{{ session('edit_id') }}">

                            <!-- Save button -->
                            <div class="text-end">
                                <button type="submit" class="btn-blue" onclick="confirmEdit()">تعديل</button>
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
                        <h5 class="modal-title" id="deleteModalLabel">
                            !تنبــــــيه</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> &times;
                    </button>
                </div>
                <div class="modal-body  mt-3 mb-5">
                    <div class="container pt-5 pb-3" style="border: 0.2px solid rgb(166, 165, 165);">
                        <form id="delete-form" action="{{ route('nationality.delete') }}" method="POST">
                            @csrf
                            <div class="form-group d-flex justify-content-center ">
                                <h5 class="modal-title " id="deleteModalLabel"> هل
                                    تريد حذف هذا المسمى ؟</h5>


                                <input type="text" id="id" hidden name="id" class="form-control">
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
            function closeModal() {
                $('#delete').modal('hide');
            }

            $('#closeButton').on('click', function() {
                closeModal();
            });
        });
    </script>
    <script>
        @if (session('modal_type') === 'add')
            $(document).ready(function() {
                $('#add').modal('show');
            });
        @endif
        $('#closeModalBtn').on('click', function() {
            $.ajax({
                url: "{{ route('modal.clearSession') }}", // The route to clear session
                type: 'GET', // Use GET for session clear (safe action)
                success: function(response) {
                    // You can check for success message if needed
                },
                error: function(xhr, status, error) {}
            });
        });
        $('#closeModalBtn1').on('click', function() {
            $.ajax({
                url: "{{ route('modal.clearSession') }}", // The route to clear session
                type: 'GET', // Use GET for session clear (safe action)
                success: function(response) {
                    // You can check for success message if needed
                },
                error: function(xhr, status, error) {}
            });
        });
        @if (session('modal_type') === 'edit')
            $(document).ready(function() {
                $('#edit').modal('show');
            });
        @endif
        function opendelete(id) {
            document.getElementById('id').value = id;
            $('#delete').modal('show');
        }

        function confirmDelete() {
            var id = document.getElementById('id').value;
            var form = document.getElementById('delete-form');

            form.submit();

        }

        function handleAction(action, id, name, code) {
            if (action) {
                document.querySelector('.btn-action').value = ''; // Reset dropdown
                if (action === "edit") {
                    openedit(id, name, code);
                } else if (action === "delete") {
                    opendelete(id);
                }
            }
        }

        function openedit(id, name, code) {
            document.getElementById('nameedit').value = name || '';
            document.getElementById('idedit').value = id || '';
            document.getElementById('codeedit').value = code || '';
            $('#edit').modal('show');
        }


        function confirmEdit() {
            var id = document.getElementById('id').value;
            var form = document.getElementById('edit-form');



        }

        function openadd() {
            $('#add').modal('show');
        }

        function confirmAdd() {
            var name = document.getElementById('nameadd').value;

            var form = document.getElementById('add-form');
            var inputs = form.querySelectorAll('[required]');
            var valid = true;

            inputs.forEach(function(input) {
                if (!input.value) {
                    valid = false;
                    input.style.borderColor =
                        'red'; // Optional: highlight empty inputs
                } else {
                    input.style.borderColor =
                        ''; // Reset border color if input is filled
                }
            });

            if (valid) {
                form.submit();
            }
        }
        $(document).ready(function() {
            $.fn.dataTable.ext.classes.sPageButton =
                'btn-pagination btn-sm'; // Change Pagination Button Class

            $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('nationality.getAllNationality') }}', // Correct URL concatenation
                columns: [{
                        data: 'country_name_ar',
                        name: 'country_name_ar' // Search will be enabled on this column
                    },
                    {
                        data: 'code',
                        name: 'code' // Search will be enabled on this column
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
                columnDefs: [{
                    targets: -1,
                    render: function(data, type, row) {
                        let options = `
        <option value="" class="text-center" style="color: gray;" selected disabled>الخيارات</option>
    `;

                        @if (Auth::user()->hasPermission('edit Country'))
                            options +=
                                `<option value="edit" class="text-center" style="color:#eb9526;">تعديل</option>`;
                        @endif
                        @if (Auth::user()->hasPermission('delete Country'))
                            options +=
                                `<option value="delete" class="text-center" style="color:#c50c0c;">حذف</option>`;
                        @endif

                        return `
        <select class="form-select form-select-sm btn-action"
                onchange="handleAction(this.value, '${row.id}', '${row.country_name_ar}', '${row.code}')"
                aria-label="Actions" style="width: auto;">
            ${options}
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
                        "sFirst": '<i class="fa fa-fast-backward" aria-hidden="true"></i>',
                        "sPrevious": '<i class="fa fa-chevron-left" aria-hidden="true"></i>',
                        "sNext": '<i class="fa fa-chevron-right" aria-hidden="true"></i>',
                        "sLast": '<i class="fa fa-step-forward" aria-hidden="true"></i>'
                    }
                },
                "pagingType": "full_numbers",
                "fnDrawCallback": function(oSettings) {
                    var api = this.api();
                    var pageInfo = api.page.info();
                    if (pageInfo.recordsTotal <= 10) {
                        $('.dataTables_paginate').css(
                            'visibility', 'hidden');
                    } else {
                        $('.dataTables_paginate').css(
                            'visibility', 'visible');
                    }
                }
            });



        });
    </script>
@endpush
