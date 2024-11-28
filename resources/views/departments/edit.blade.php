<style>
    .div-info {
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
        width: 200px;
        height: 200px;
        background-color: #F6F7FD;
        border: 1px solid #D9D9D9 !important;
    }

    .div-info-padding {
        padding: 3px 0;
        direction: initial;
        font-family: Almarai;
        font-size: 24px;
        font-weight: 700;
        line-height: 36px;
        text-align: right;

    }

    .div-info-padding b span {
        color: #032F70;
    }

    .paragraph {
        display: flex;
        justify-content: end;
        font-weight: 700;
        font-size: 25px;
    }

    #credit-table thead {
        text-align: right !important;
        font-size: 22px !important;
        font-weight: 400 !important;
        color: #3c3c3d !important;
    }
</style>
@extends('layout.main')
@section('title')
    تعديل
@endsection
@section('content')
    <main>
        <div class="row" dir="rtl">
            <div class="container col-11" style="background-color:transparent;">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/">الرئيسيه</a>
                        </li>
                        <li class="breadcrumb-item">
                            <?php /* <a href="{{ route('departments.index', ['id' => $department->sector_id]) }}"> */?>
                            <a href="{{ route('departments.index', $department->sectors->uuid) }}">
                                {{-- {{ $department->name }} --}} الأدارات
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page"><a href=""> تعديل ادارة</a></li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row ">
            <div class="container welcome col-11">
                <p> تعديل أداره الرئيسيه </p>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="container  col-11 mt-3 py-4 ">
                <div class="container col-11" style="border:0.5px solid #C7C7CC;">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    <form class="edit-grade-form" id="Qta3-form" action="{{ route('departments.update', $department) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        @if ($errors->any())
                            <div class="alert alert-danger"dir="rtl">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="form-row mx-3 mt-4 d-flex justify-content-center">
                            <div class="form-group col-md-12 mx-md-2">
                                <label for="sector">اختر القطاع </label>
                                <input type="text" name="name" id="name" class="form-control"
                                    value="{{ $department->sectors->name }}" disabled>
                                <input type="hidden" name="sector" id="sector" class="form-control"
                                    value="{{ $department->sectors->id }}">
                                <input type="hidden" name="department_id" id="department_id" class="form-control"
                                    value="{{ $department->id }}">
                                @error('sector')
                                    <div class="alert alert-danger">{{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group col-md-12 mx-md-2">
                                <label for="name">أسم الأداره الرئيسية</label>
                                <input type="text" name="name" class="form-control" autocomplete="one-time-code"
                                    value="{{ $department->name }}">
                                @error('name')
                                    <div class="alert alert-danger">{{ $message }}
                                    </div>
                                @enderror
                            </div>


                            <div class="form-group col-md-12 mx-md-2" id="manager">
                                <label for="mangered">رقم ملف المدير</label>
                                <input type="text" name="mangered" id="mangered" class="form-control"
                                    autocomplete="one-time-code"
                                    value="{{ old('mangered', $department->manager ? $fileNumber : '') }}">

                                @error('mangered')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group col-md-12 mx-md-2" id="email_field" style="display: none;"
                                @error('email') style="display: block;" @enderror>
                                <label class="pb-3" for="email">الأيميل</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                                @error('email')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group col-md-12 mx-md-2" id="manager_details">
                                <div class="col-12 div-info d-flex justify-content-between" style="direction: rtl">
                                    <div class="col-7">
                                        <div class="col-12 div-info-padding"><b>الرتبه : <span></span></b></div>
                                        <div class="col-12 div-info-padding"><b>المسمى الوظيفى: <span></span></b>
                                        </div>
                                    </div>
                                    <div class="col-5">
                                        <div class="col-12 div-info-padding"><b>الأسم: <span></span></b></div>
                                        <div class="col-12 div-info-padding"><b>الهاتف: <span></span></b></div>
                                        <div class="col-12 div-info-padding" style="direction: rtl"><b>الأيميل:
                                                <span></span></b></div>

                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-12 mx-md-2">
                                <label for="description">الوصف </label>
                                <input type="text" name="description" class="form-control" autocomplete="one-time-code"
                                    value="{{ $department->description }}">
                                @error('description')
                                    <div class="alert alert-danger">
                                        {{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group col-md-12 mx-md-2">
                                <label for="file_number" class="col-12"> أرقام
                                    الملفات</label>
                                <textarea class="form-control" name="file_number" id="file_number" style="height: 100px">
                                            @foreach ($employees as $employee)
{{ $employee->file_number }}
@endforeach
                                </textarea>

                            </div>
                            <div class="form-group col-md-12 mx-md-2">
                                <label for="">صلاحيه الحجز</label>
                                <div class="d-flex mt-3 " dir="rtl">
                                    <input type="checkbox" class="toggle-radio-buttons mx-2" value="1"
                                        id="fullBooking" @if ($department->reservation_allowance_type == 1 || $department->reservation_allowance_type == 3) checked @endif name="part[]">
                                    <label for="fullBooking"> حجز كلى</label>
                                    <input type="checkbox" class="toggle-radio-buttons mx-2" value="2"
                                        name="part[]" id="partialBooking"
                                        @if ($department->reservation_allowance_type == 2 || $department->reservation_allowance_type == 3) checked @endif>
                                    <label for="partialBooking">حجز جزئى</label>
                                    <input type="checkbox" class="toggle-radio-buttons mx-2" value="3"
                                        name="part[]" id="noBooking" @if ($department->reservation_allowance_type == 4) checked @endif>
                                    <label for="noBooking">لا يوجد بدل حجز</label>
                                    {{-- @error('budget')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror --}}
                                </div>
                            </div>



                            <div class="form-group col-md-12 mx-md-2" dir="rtl">

                                <h4 class="mb-3 d-flex justify-content-start">ميزانيه الحجز</h4>


                                <div class="d-flex mt-3">
                                    <label for="notFree" class="d-flex align-items-center">
                                        <input type="radio" class="toggle-radio-buttons mx-2"
                                            {{ (float) $department->reservation_allowance_amount > 0.0 ? 'checked' : '' }}
                                            name="budget_type" value="1" id="notFree" style="height:20px;">
                                        ميزانيه محدده

                                    </label>

                                    <label for="free" class="d-flex align-items-center">
                                        <input type="radio" class="toggle-radio-buttons mx-2" name="budget_type"
                                            {{ (float) $department->reservation_allowance_amount == 0.0 ? 'checked' : '' }}
                                            value="2" id="free" style="height:20px;">ميزانيه غير محدده

                                    </label>
                                </div>
                            </div>

                            <div class="form-group col-md-12 mx-md-2" id="budgetField"
                                style={{ (float) $department->reservation_allowance_amount > 0.0 ? 'display: block' : 'display: none;' }}>
                                <label class="d-flex pb-3" for="budget">ميزانية بدل حجز</label>
                                <input type="text" name="budget" class="form-control"
                                    value=" {{ (float) $department->reservation_allowance_amount > 0.0 ? $department->reservation_allowance_amount : 00.0 }}"
                                    id="budget" autocomplete="one-time-code">
                                @error('budget')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                        <div class="form-group col-md-12 mx-md-2">
                            <button class="btn-blue " type="submit"> اضافة </button>
                        </div>


                    </form>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $('.select2').select2({
            dir: "rtl"
        });
        $(document).ready(function() {
            var selectedManagerId = $('#mangered').val();

            if (selectedManagerId) {
                $('#email_field').show();
                fetchManagerDetails(selectedManagerId, false);

                var existingEmail = @json(old('mangered', $department->manager ? $email : null));
                var existingBudget = @json(old('budget', $department->reservation_allowance_amount ? $department->reservation_allowance_amount : ''));

                if (existingEmail) {
                    $('#email_field').css({
                        display: 'block',
                        visibility: 'visible',
                        opacity: 1
                    });
                    $('#email').val(existingEmail);
                }

                if (existingBudget) {
                    $('#notFree').prop('checked', true);
                    $('#budgetField').css({
                        display: 'block',
                        visibility: 'visible',
                        opacity: 1
                    });
                    $('#budget').val(existingBudget);
                } else {
                    $('#Free').prop('checked', true);
                }
            } else {
                $('#manager_details').hide();
                $('#email_field').hide();
            }
        });

        function fetchManagerDetails(managerId, skipDepartmentCheck = true) {

            if (managerId) {
                var departmentId = $('#department_id').val();
                var sectorId = $('#sector').val();
                var is_EditPages = true;

                $.ajax({
                    url: '/get-manager-details/' + managerId + '?skipDepartmentCheck=' + skipDepartmentCheck +
                        '?isEditPage=' + true,
                    type: 'GET',
                    data: {
                        department_id: departmentId,
                        sector_id: sectorId,
                        isEditPages: is_EditPages,
                        skipDepartmentCheck: skipDepartmentCheck
                    }, // Send sector_id to the backend
                    success: function(data) {
                        $('#manager_details').find('span').eq(0).text(
                            data.rank);
                        $('#manager_details').find('span').eq(1).text(
                            data.job_title);
                        $('#manager_details').find('span').eq(2).text(
                            data.name);
                        $('#manager_details').find('span').eq(3).text(
                            data.phone);
                        $('#manager_details').find('span').eq(4).text(
                            data.email);
                        $('#manager_details').show();

                        $('#email_field').show();

                        // Show password and rule fields for employees
                        if (data.email) {

                            if (data.email === 'لا يوجد بريد الكتروني') {
                                $('#email').val('');

                            } else {
                                $('#email').val(data.email);

                            }
                        } else {
                            // $('#email_field').hide();
                            $('#email').val('');
                        }


                        // Handle transfer logic
                        if (data.transfer) {
                            Swal.fire({
                                title: 'تحذير',
                                text: data.warning,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'نعم, نقل',
                                cancelButtonText: 'إلغاء',
                                confirmButtonColor: '#3085d6'
                            }).then((result) => {
                                if (result.isConfirmed) {

                                } else {
                                    // Handle cancel action: clear the manager input field
                                    $('#mangered').val(''); // Clear the input field
                                    $('#manager_details').hide(); // Hide the manager details
                                    $('#email_field').hide();
                                }
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        // Display error or warning message based on the response
                        var response = JSON.parse(xhr.responseText);
                        // Handle error message
                        if (response.error) {
                            Swal.fire({
                                title: 'تحذير',
                                text: response.error,
                                icon: 'error',
                                confirmButtonText: 'إلغاء',
                                confirmButtonColor: '#3085d6'
                            }).then((result) => {
                                // User clicked "إلغاء", clear the input field
                                $('#mangered').val('');
                                $('#manager_details').hide();
                                $('#email_field').hide();
                                $('#email').val('');
                            });
                        }
                    }
                });
            } else {
                // Reset the manager details if no manager ID is provided
                $('#manager_details').hide();
                $('#email_field').hide();
                $('#email').hide();
            }
        }

        $('#manager_details').hide();
        $('#email_field').hide();

        // Use 'blur' event to trigger the check when the input field loses focus
        $('#mangered').on('blur', function() {
            var managerId = $(this).val();
            $('#email').val('');
            fetchManagerDetails(managerId);
        });

        var selectedManagerId = $('#mangered').val();
        if (selectedManagerId) {
            fetchManagerDetails(selectedManagerId);
        }
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const noBookingCheckbox = document.getElementById('noBooking');
            const fullBookingCheckbox = document.getElementById(
                'fullBooking');
            const partialBookingCheckbox = document.getElementById(
                'partialBooking');

            noBookingCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    fullBookingCheckbox.checked = false;
                    partialBookingCheckbox.checked = false;
                }
            });

            [fullBookingCheckbox, partialBookingCheckbox].forEach(
                checkbox => {
                    checkbox.addEventListener('change', function() {
                        if (this.checked) {
                            noBookingCheckbox.checked = false;
                        }
                    });
                });
        });
        window.addEventListener('load', function() {
            const notFree = document.getElementById('notFree');
            const free = document.getElementById('free');
            const budgetField = document.getElementById('budgetField');
            const budget = document.getElementById('budget'); // Input field for budget

            // Check initial state of radio buttons on page load
            if (notFree.checked) {
                budgetField.style.display = 'block'; // Show the budget field
            } else if (free.checked) {
                budgetField.style.display = 'none'; // Hide the budget field
                budget.value = ''; // Clear the budget field
            }

            // Listen for changes on the radio buttons
            notFree.addEventListener('change', function() {
                if (notFree.checked) {
                    budgetField.style.display = 'block'; // Show the budget field
                }
            });

            free.addEventListener('change', function() {
                if (free.checked) {
                    budgetField.style.display = 'none'; // Hide the budget field
                    budget.value = ''; // Clear the budget field
                }
            });
        });

        window.addEventListener('load', function() {
            const emailField = document.getElementById('email_field');
            const emailInput = document.getElementById('email');

            // Function to toggle the 'required' attribute based on email field visibility
            function toggleEmailRequired() {
                if (emailField.style.display === 'block') {
                    emailInput.setAttribute('required', 'required');
                } else {
                    emailInput.removeAttribute('required');
                }
            }


            // Call toggle function after changing visibility
            toggleEmailRequired();

            mangeredInput.addEventListener('input', toggleEmailField);


        });
    </script>
@endsection
