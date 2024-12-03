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
    القطاعات
@endsection

@section('content')
    <div class="row" dir="rtl">
        <div class="container col-11" style="background-color:transparent;">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">الرئيسيه</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('sectors.index') }}">القطاعات</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><a href=""> تعديل قطاع</a></li>
                </ol>
            </nav>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form class="edit-grade-form" id="Qta3-form" action="{{ route('sectors.update', $data) }}" method="POST">
        @csrf
        @method('POST') <!-- This line indicates it's an update -->
        <div class="row" dir="rtl">
            <div id="first-container" class="container moftsh col-11 py-2">
                <div class="form-row mx-2 mb-2">
                    <h3 class=" px-md-5 px-3">اضف قطاع</h3>
                    <div class="input-group moftsh px-md-5 px-3 pt-3">
                        <label class="pb-3" for="name">ادخل اسم القطاع</label>
                        <input type="hidden" name="id" value="{{ $data->id }}">
                        <input type="text" id="name" name="name" class="form-control"
                            value="{{ old('name', $data->name) }}" placeholder="قطاع واحد" required
                            autocomplete="one-time-code" />
                        @error('name')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <span class="text-danger span-error" id="name-error"></span>
                    </div>
                </div>

                <div class="input-group moftsh px-md-5 px-3 pt-3" id="manager">
                    <label for="mangered">رقم ملف المدير</label>
                    <input type="text" name="mangered" id="mangered" class="form-control"
                        value="{{ old('mangered', $data->manager ? $fileNumber : null) }}" autocomplete="one-time-code">
                    @error('mangered')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="input-group moftsh px-md-5 px-3 pt-3" id="email_field"
                    style="{{ old('mangered', $data->manager ? 'display: block;' : 'display: none;') }}">
                    <label class="pb-3 w-100" for="email">الايميل</label>
                    <input type="email" name="email" id="email"
                        value="{{ old('mangered', $data->manager ? $email : null) }}" class="form-control">
                    @error('email')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="input-group moftsh px-md-5 px-3 pt-3" id="manager_details" style="display: none;">
                    <div class="col-12 div-info d-flex justify-content-between" style="direction: rtl">
                        <div class="col-7">
                            <div class="col-12 div-info-padding"><b>الرتبه :
                                    <span></span></b></div>
                            <div class="col-12 div-info-padding"><b>المسمى الوظيفى:
                                    <span></span></b></div>
                        </div>
                        <div class="col-5">
                            <div class="col-12 div-info-padding"><b>الأسم:
                                    <span></span></b></div>
                            <div class="col-12 div-info-padding"><b>الهاتف:
                                    <span></span></b></div>
                            <div class="col-12 div-info-padding" style="direction: rtl"><b>الأيميل:
                                    <span></span></b></div>
                        </div>
                    </div>
                </div>

                <div class="input-group moftsh px-md-5 px-3 pt-3">
                    <label for="Civil_number" class="col-12"> أرقام الملفات</label>
                    <textarea class="form-control" name="Civil_number" id="Civil_number"
                        style="height: 100px;background-color: #F8F8F8;border-radius: 10px !important;">
                        @foreach ($employees as $employee)
{{ $employee->file_number }}
@endforeach
                    </textarea>
                </div>
                <div class="input-group moftsh px-md-5 px-3 pt-3">
                    <label for="" class="col-12">ميزانيه الحجز</label>
                    <div class="d-flex mt-3" dir="rtl">
                        <input type="radio" class="toggle-radio-buttons mx-2" name="budget_type" value="1"
                            id="notFree" style="height:30px;"
                            {{ old('budget_type', $data->reservation_allowance_amount != '0.00' ? 1 : null) == 1 ? 'checked' : '' }}
                            @if ($errors->has('budget')) checked @endif> <label for="notFree" class="col-12">ميزانيه
                            محدده</label>

                        <input type="radio" class="toggle-radio-buttons mx-2" name="budget_type" value="2"
                            id="free" style="height:30px;"
                            {{ old('budget_type', (float) $data->reservation_allowance_amount == 0.0 ? 2 : null) == 2 ? 'checked' : '' }}>
                        <label for="free" class="col-12">ميزانيه غير محدده</label>
                    </div>
                </div>

                <div class="input-group moftsh px-md-5 px-3 pt-3" id="budgetField"
                    style="{{ old('budget_type', (float) $data->reservation_allowance_amount > 0.0 ? 1 : null) == 1 ? 'display: block;' : 'display: none;' }}">
                    <label class="d-flex pb-3" for="budget">ميزانية بدل حجز</label>
                    <input type="text" name="budget" class="form-control"
                        value="{{ old('budget', $data->reservation_allowance_amount > 0.0 ? $data->reservation_allowance_amount : '') }}"
                        id="budget" autocomplete="one-time-code">

                </div>
                @error('budget')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
                <div class="input-group moftsh px-md-5 px-3 pt-3">
                    <label for="" class="col-12">صلاحيه الحجز</label>
                    <div class="d-flex mt-3" dir="rtl">
                        <input type="checkbox" class="toggle-radio-buttons mx-2" value="1" id="fullBooking"
                            name="part[]" style="height:30px;"
                            {{ in_array(1, old('part', $data->reservation_allowance_type == 1 || $data->reservation_allowance_type == 3 ? [1] : [])) ? 'checked' : '' }}>
                        <label for="fullBooking" class="col-12">حجز كلى</label>

                        <input type="checkbox" class="toggle-radio-buttons mx-2" value="2" id="partialBooking"
                            name="part[]" style="height:30px;"
                            {{ in_array(2, old('part', $data->reservation_allowance_type == 2 || $data->reservation_allowance_type == 3 ? [2] : [])) ? 'checked' : '' }}>
                        <label for="partialBooking" class="col-12">حجز جزئى</label>

                        <input type="checkbox" class="toggle-radio-buttons mx-2" value="3" id="noBooking"
                            name="part[]" style="height:30px;"
                            {{ in_array(3, old('part', $data->reservation_allowance_type == 4 ? [3] : [])) ? 'checked' : '' }}>
                        <label for="noBooking" class="col-12">لا يوجد بدل حجز</label>
                    </div>
                    @error('part')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="container col-11">
                    <div class="form-row d-flex justify-content-end mt-4 mb-3">
                        <button type="submit" class="btn-blue">
                            <img src="{{ asset('frontend/images/white-add.svg') }}" alt="img" height="20px"
                                width="20px">
                            اضافة
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if ($errors->any())
        <script>
            $(document).ready(function() {
                var selectedManagerId = $('#mangered').val();

                if (selectedManagerId) {
                    $('#email_field').show();
                    fetchManagerDetails(selectedManagerId, false);

                    var existingEmail = @json(old('email'));
                    var existingBudget = @json(old('budget'));

                    if (existingEmail) {
                        $('#email_field').show();
                        $('#email').val(@json(old('email')));
                    }


                } else {
                    $('#manager_details').hide();
                    $('#email_field').hide();
                }
            });
        </script>
    @endif
    <script>
        // Initialize select2 for RTL
        $('.select2').select2({
            dir: "rtl"
        });
        $(document).ready(function() {
            var selectedManagerId = $('#mangered').val();

            if (selectedManagerId) {
                $('#email_field').show();
                fetchManagerDetails(selectedManagerId, false);

                var existingEmail = @json(old('mangered', $data->manager ? $email : null));
                var existingBudget = @json(old('budget', $data->reservation_allowance_amount ? $data->reservation_allowance_amount : ''));

                if (existingEmail) {
                    $('#email_field').css({
                        display: 'block',
                        visibility: 'visible',
                        opacity: 1
                    });
                    $('#email').val(existingEmail);
                }


            } else {
                $('#manager_details').hide();
                $('#email_field').hide();
            }
        });




        // Modify fetchManagerDetails to accept an optional second parameter
        function fetchManagerDetails(managerId, skipDepartmentCheck = true) {
            sector = {{ $data->id }};
            if (managerId) {
                $.ajax({
                    url: '/get-manager-sector-details/' + managerId + '/' +
                        sector + '?skipDepartmentCheck=' + skipDepartmentCheck,
                    type: 'GET',
                    success: function(data) {
                        // Show the manager details div
                        $('#manager_details').show();

                        // Populate manager details
                        $('#manager_details').find('span').eq(0).text(data.rank);
                        $('#manager_details').find('span').eq(1).text(data.job_title);
                        $('#manager_details').find('span').eq(2).text(data.name);
                        $('#manager_details').find('span').eq(3).text(data.phone);
                        $('#manager_details').find('span').eq(4).text(data.email);

                        // Show/hide password and rule fields
                        if (data.email) {
                            $('#email_field').show();

                            if (data.email === 'لا يوجد بريد الكتروني') {
                                $('#email').val('');

                            } else {
                                $('#email').val(data.email);

                            }
                            $('#email').val(data.email);
                        } else {
                            $('#email_field').hide();
                            $('#email').val('');
                        }
                    },
                    error: function(xhr) {
                        // Handle different error responses
                        if (xhr.status === 405) {
                            Swal.fire({
                                title: 'تحذير',
                                text: xhr.responseJSON.error || 'عفوا هذا المستخدم غير موجود',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'نعم, استمر',
                                cancelButtonText: 'لا',
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                // willClose: () => {
                                //     // Handle the case when the user does not select Yes or No
                                //     $('#mangered').val(''); // Clear manager input field
                                //     $('#email_field').hide(); // Hide the email field
                                //     $('#email').val(''); // Clear the email input field
                                //     $('#email').removeAttr(
                                //     'required'); // Remove the 'required' attribute
                                //     $('#manager_details').hide(); // Hide manager details
                                // }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    fetchManagerDetails(managerId, false);
                                    $('#email_field').show();
                                    $('#email').show();
                                    $('#email').val(result.email);

                                    $('#manager_details').show();
                                    // Populate manager details
                                    $('#manager_details').find('span').eq(0).text(result.rank);
                                    $('#manager_details').find('span').eq(1).text(result.job_title);
                                    $('#manager_details').find('span').eq(2).text(result.name);
                                    $('#manager_details').find('span').eq(3).text(result.phone);
                                    $('#manager_details').find('span').eq(4).text(result.email);

                                } else {
                                    // Hide details if user cancels
                                    $('#mangered').val(''); // Clear manager input field
                                    $('#manager_details').hide();
                                    $('#email_field').hide();
                                    $('#email').val(''); // Clear password field
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'خطأ',
                                text: 'هذا الموظف غير موجود و يرجى أدخال رقم ملف صحيح',
                                icon: 'error',
                                confirmButtonText: 'إلغاء',
                                confirmButtonColor: '#3085d6',
                                // willClose: () => {
                                //     // Handle the case when the user does not select Yes or No
                                //     $('#mangered').val(''); // Clear manager input field
                                //     $('#email_field').hide(); // Hide the email field
                                //     $('#email').val(''); // Clear the email input field
                                //     $('#email').removeAttr(
                                //     'required'); // Remove the 'required' attribute
                                //     $('#manager_details').hide(); // Hide manager details
                                // }
                            });
                            $('#mangered').val('');
                        }
                    }
                });
            } else {
                // Hide details if no manager ID
                $('#manager_details').hide();
                $('#email_field').hide();
                $('#email').val('');
            }
        }

        // Bind the blur event on the manager field to fetch the details
        $('#mangered').bind('blur', function() {
            var managerId = $(this).val();
            $('#email').val(''); // Clear previous input
            fetchManagerDetails(managerId, true); // Fetch new details
        });
    </script>
    <script>
        document.getElementById('notFree').addEventListener('change', function() {
            const budgetField = document.getElementById('budgetField');
            if (this.checked) {
                budgetField.style.display = 'block'; // Show the budget field for "ميزانيه محدده"
            }
        });

        document.getElementById('free').addEventListener('change', function() {
            const budgetField = document.getElementById('budgetField');
            if (this.checked) {
                budgetField.style.display = 'none'; // Hide the budget field for "ميزانيه غير محدده"
            }
        });

        // Ensure the correct field is displayed on page load based on the selected radio button
        window.addEventListener('load', function() {
            const notFree = document.getElementById('notFree');
            const free = document.getElementById('free');
            const budgetField = document.getElementById('budgetField');
            if (notFree.checked) {
                budgetField.style.display = 'block';
            } else if (free.checked) {
                budgetField.style.display = 'none';
            }
        });
        document.addEventListener("DOMContentLoaded", function() {
            const noBookingCheckbox = document.getElementById('noBooking');
            const fullBookingCheckbox = document.getElementById('fullBooking');
            const partialBookingCheckbox = document.getElementById('partialBooking');

            noBookingCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    fullBookingCheckbox.checked = false;
                    partialBookingCheckbox.checked = false;
                }
            });

            [fullBookingCheckbox, partialBookingCheckbox].forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        noBookingCheckbox.checked = false;
                    }
                });
            });
        });


        window.addEventListener('load', function() {
            const mangeredInput = document.getElementById('mangered');
            const emailField = document.getElementById('email_field');
            const emailInput = document.getElementById('email');

            // Function to toggle visibility and 'required' attribute
            function toggleEmailField() {
                if (mangeredInput.value.trim() !== '') {
                    emailField.style.display = 'block';
                    emailInput.setAttribute('required', 'required');
                } else {
                    emailField.style.display = 'none';
                    emailInput.removeAttribute('required');
                }
            }

            // Call the function on page load to set the initial state
            toggleEmailField();

            // Attach event listener to mangered input to detect changes
            mangeredInput.addEventListener('input', toggleEmailField);
        });
    </script>
@endsection
