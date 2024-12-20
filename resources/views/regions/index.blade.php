@extends('layout.main')
@push('style')

@endpush
@section('title')
    المناطق
@endsection
@section('content')
    <section>
        <div class="row">

            <div class="container welcome col-11">
                <div class="d-flex justify-content-between">
                    <p> المنـــاطق</p>
                    @if (Auth::user()->hasPermission('create Region'))
                        <button type="button" class="btn-all  " onclick="openadd()" style="color: #0D992C;">
                            اضافة منطقة جديدة <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <br>
        <div class="row">
            <div class="container  col-11 mt-3 p-0 ">

                <div class="row " dir="rtl">
                    <div class="form-group mt-4  mx-md-2 col-12 d-flex ">

                        <div class="form-group moftsh  mx-3  d-flex">
                            <h4 style="margin-left:10px;line-height: 1.8;"> تصفية حسب </h4>
                            <select name="government-select" id="government-select" onchange="filterRegions()"
                                class=" form-group mx-md-2 btn-all  custom-select custom-select-lg mb-3 select2   "
                                style="text-align: center; color:#ff8f00;height: 40px;font-size: 19px; padding-inline:10px;">
                                <option value="" selected disabled> المحافظه</option>
                                @foreach (getgovernments() as $government)
                                    <option value="{{ $government->hash_id }}"
                                        @if ($government->hash_id == $id) selected @endif>
                                        {{ $government->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
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
                                        <th>المحافظه التابعه لها</th>
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
                        <h5 class="modal-title" id="lable"> أضافه منطقه جديد</h5>

                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> &times;
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container pt-4 pb-4" style="border: 0.2px solid rgb(166, 165, 165);">
                        <form class="edit-grade-form" id="add-form" action=" {{ route('regions.store') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="nameadd">الاسم</label>
                                <input type="text" id="nameadd" name="nameadd" class="form-control" required>
                                @error('nameadd')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="governmentid">المحافظات </label>
                                <select name="governmentid" id="governmentid"
                                    class=" form-group col-md-12 custom-select custom-select-lg mb-3 select2 "
                                    style="border: 0.2px solid rgb(199, 196, 196); width:100% !important;" required>
                                    <option value="">اختر المحافظه</option>
                                    @foreach (getgovernments() as $government)
                                        <option value="{{ $government->id }}">{{ $government->name }}</option>
                                    @endforeach
                                </select>
                                @error('government-id')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror

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
                        <h5 class="modal-title" id="lable"> تعديل اسم المنطقه ؟</h5>

                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> &times;
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container pt-4 pb-4" style="border: 0.2px solid rgb(166, 165, 165);">
                        <form class="edit-grade-form" id="edit-form" action=" {{ route('regions.update') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="name">الاسم</label>
                                <input type="text" id="nameedit" value="" name="name" class="form-control"
                                    dir="rtl" required>
                                <input type="text" id="idedit" value="" name="id" hidden
                                    class="form-control">

                            </div>
                            <div class="form-group">
                                <label for="government">المحافظات</label>
                                <select name="government" id="government" class="form-group col-md-12 " required>
                                    <option value="">اختر المحافظه</option>
                                    @foreach (getgovernments() as $government)
                                        <option value="{{ $government->id }}">{{ $government->name }}</option>
                                    @endforeach
                                </select>
                                @error('government')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror

                            </div>
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
@endsection
@push('scripts')

    <script>
        $('.select2').select2({
            dir: "rtl"
        });

        function handleAction(action, id, name, government) {
            if (action) {
                document.querySelector('.btn-action').value = ''; // Reset dropdown
                if (action === "edit") {
                    openedit(id, name, government);
                } else if (action === "delete") {
                    opendelete(id);
                }
            }
        }

        function openedit(id, name, government) {
            document.getElementById('nameedit').value = name || '';
            document.getElementById('idedit').value = id || '';
            document.getElementById('government').value = government || '';
            $('#edit').modal('show');
        }

        function confirmEdit() {
            var id = document.getElementById('idedit').value;
            var name = document.getElementById('nameedit').value;
            var government = document.getElementById('government').value;
            var form = document.getElementById('edit-form');
        }

        function openadd() {

            $('#add').modal('show');
            $('#exampleModal').on('shown.bs.modal', function() {
                $('#government-select').select2({
                    width: 'resolve',
                    placeholder: 'اختر المحافظه',
                    allowClear: true
                });
            });
        }

        function confirmAdd() {
            var name = document.getElementById('nameadd').value;
            var government = document.getElementById('governmentid').value;
            var form = document.getElementById('add-form');
        }
        var table;

        $(document).ready(function() {
            $.fn.dataTable.ext.classes.sPageButton = 'btn-pagination btn-sm'; // Change Pagination Button Class

            table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('getAllregions') }}',
                    data: function(d) {
                        d.government_id = $('#government-select').val(); // Add government_id to request
                    }
                },
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'government_name',
                        name: 'government_name'
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
                        console.log(row)
                        let options = `
        <option value="" class="text-center" style="color: gray;" selected disabled>الخيارات</option>
    `;

                        @if (Auth::user()->hasPermission('edit Region'))
                            options +=
                                `<option value="edit" class="text-center" style="color:#eb9526;">تعديل</option>`;
                        @endif

                        return `
        <select class="form-select form-select-sm btn-action"
                onchange="handleAction(this.value, '${row.id}', '${row.name}', '${row.government_id}')"
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
                    console.log('Page ' + this.api().page.info().pages)
                    var page = this.api().page.info().pages;
                    console.log($('#users-table tr').length);
                    if (page == 1) {
                        //   $('.dataTables_paginate').hide();//css('visiblity','hidden');
                        $('.dataTables_paginate').css('visibility', 'hidden'); // to hide

                    }
                }
            });

            $('#government-select').change(function() {
                table.ajax.reload(); // Reload DataTable data on dropdown change
            });
        });

        function filterRegions() {
            if (table) {
                table.ajax.reload(); // Reload DataTable with new filter
            }
        }
    </script>
@endpush
