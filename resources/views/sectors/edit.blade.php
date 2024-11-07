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

    <br>

    <form class="edit-grade-form" id="Qta3-form" action="{{ route('sectors.update', $data->id) }}" method="POST">
        @csrf
        @method('POST') <!-- This line indicates it's an update -->
        <div class="row" dir="rtl">
            <div id="first-container" class="container moftsh col-11 mt-3 p-0 pb-3">
                <div class="form-row mx-2 mb-2">
                    <h3 class="pt-3 px-md-5 px-3">اضف قطاع</h3>
                    <div class="input-group moftsh px-md-5 px-3 pt-3">
                        <label class="pb-3" for="name">ادخل اسم القطاع</label>
                        <input type="hidden" name="id" value="{{ $data->id }}">
                        <input type="text" id="name" name="name" class="form-control"
                            value="{{ old('name', $data->name) }}" placeholder="قطاع واحد" required
                            autocomplete="one-time-code" />
                        <span class="text-danger span-error" id="name-error"></span>
                    </div>
                </div>

                <div class="input-group moftsh px-md-5 px-3 pt-3">
                    <label class="pb-3" for="budget">ميزانية بدل حجز</label>
                    <input type="text" name="budget" class="form-control"
                        value="{{ old('budget', $data->reservation_allowance_amount) }}" autocomplete="one-time-code">
                    @error('budget')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="input-group moftsh px-md-5 px-3 pt-3" id="manager">
                    <label for="mangered">رقم ملف المدير</label>
                    <input type="text" name="mangered" id="mangered" class="form-control"
                        value="{{ old('mangered', $data->manager ? $fileNumber : null) }}"
                        autocomplete="one-time-code">
                    @error('mangered')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="input-group moftsh px-md-5 px-3 pt-3" id="password_field" style="display: none;">
                    <label class="pb-3" for="password">كلمة المرور</label>
                    <input type="password" name="password" id="password" class="form-control">
                    @error('password')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="input-group moftsh px-md-5 px-3 pt-3" id="rule_field" style="display: none;">
                    <label class="pb-3" for="rule">الصلاحيات</label>
                    <select name="rule" id="rule" class="form-control">
                        <option value="">اختار الصلاحية</option>
                        @foreach ($rules as $rule)
                            <option value="{{ $rule->id }}" {{ old('rule') == $rule->id ? 'selected' : '' }}>
                                {{ $rule->name }}</option>
                        @endforeach
                    </select>
                    @error('rule')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="input-group moftsh px-md-5 px-3 pt-3" id="manager_details" style="display: none;">
                    <div class="col-12 div-info d-flex justify-content-between" style="direction: rtl">
                        <div class="col-7">
                            <div class="col-12 div-info-padding"><b>الرتبه :
                                    <span></span></b></div>
                            <div class="col-12 div-info-padding"><b>الأقدميه :
                                    <span></span></b></div>
                            <div class="col-12 div-info-padding"><b>المسمى الوظيفى:
                                    <span></span></b></div>
                        </div>
                        <div class="col-5">
                            <div class="col-12 div-info-padding"><b>الأسم:
                                    <span></span></b></div>
                            <div class="col-12 div-info-padding"><b>الهاتف:
                                    <span></span></b></div>
                            <div class="col-12 div-info-padding"><b>الأيميل:
                                    <span></span></b></div>
                        </div>
                    </div>
                </div>

                <div class="input-group moftsh px-md-4 px-3 pt-3">
                    <label for="Civil_number" class="col-12"> أرقام الملفات</label>
                    <textarea class="form-control" name="Civil_number" id="Civil_number" style="height: 100px">
                        @foreach ($employees as $employee)
{{ $employee->file_number }}
@endforeach
                    </textarea>
                </div>

                <div class="input-group moftsh px-md-5 px-3 pt-3">
                    <label for="" class="col-12">صلاحيه الحجز</label>
                    <div class="d-flex mt-3" dir="rtl">
                        <input type="checkbox" class="toggle-radio-buttons mx-2" value="1" id="fullBooking"
                            style="height:30px;" @if ($data->reservation_allowance_type == 1 || $data->reservation_allowance_type == 3) checked @endif name="part[]">
                        <label for="fullBooking" class="col-12"> حجز كلى</label>

                        <input type="checkbox" class="toggle-radio-buttons mx-2" style="height:30px;" value="2"
                            id="partialBooking" @if ($data->reservation_allowance_type == 2 || $data->reservation_allowance_type == 3) checked @endif name="part[]">
                        <label for="partialBooking" class="col-12">حجز جزئى</label>

                        <input type="checkbox" class="toggle-radio-buttons mx-2" value="3" id="noBooking"
                            style="height:30px;" @if ($data->reservation_allowance_type == 4) checked @endif name="part[]">
                        <label for="noBooking" class="col-12">لا يوجد حجز</label>

                        @error('part')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
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

    <script>
        // Initialize select2 for RTL
        $('.select2').select2({
            dir: "rtl"
        });

        // On page load: check if there's a selected manager ID
        $(document).ready(function() {
            var selectedManagerId = $('#mangered').val();
            if (selectedManagerId) {
                $('#password_field').show();
                $('#rule_field').show();
                fetchManagerDetails(selectedManagerId, false); // Pass true to skip the department check
            } else {
                $('#manager_details').hide();
                $('#password_field').hide();
                $('#rule_field').hide();
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
                        $('#manager_details').find('span').eq(1).text(data.seniority);
                        $('#manager_details').find('span').eq(2).text(data.job_title);
                        $('#manager_details').find('span').eq(3).text(data.name);
                        $('#manager_details').find('span').eq(4).text(data.phone);
                        $('#manager_details').find('span').eq(5).text(data.email);

                        // Show/hide password and rule fields
                        if (data.isEmployee) {
                            $('#password_field').show();
                            $('#rule_field').show();
                        } else {
                            $('#password_field').hide();
                            $('#rule_field').hide();
                            $('#password').val(''); // Clear password field
                            $('#rule').val(''); // Clear rule field
                        }
                    },
                    error: function(xhr) {
                        // Handle different error responses
                        if (xhr.status === 404) {
                            Swal.fire({
                                title: 'تحذير',
                                text: xhr.responseJSON.error || 'عفوا هذا المستخدم غير موجود',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'نعم, استمر',
                                cancelButtonText: 'لا',
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    fetchManagerDetails(managerId, false);
                                    $('#password_field').show();
                                    $('#rule_field').show();
                                    $('#password').show();
                                    $('#rule').show();
                                    $('#manager_details').show();
                                    // Populate manager details
                                    $('#manager_details').find('span').eq(0).text(result.rank);
                                    $('#manager_details').find('span').eq(1).text(result.seniority);
                                    $('#manager_details').find('span').eq(2).text(result.job_title);
                                    $('#manager_details').find('span').eq(3).text(result.name);
                                    $('#manager_details').find('span').eq(4).text(result.phone);
                                    $('#manager_details').find('span').eq(5).text(result.email);

                                } else {
                                    // Hide details if user cancels
                                    $('#mangered').val(''); // Clear manager input field
                                    $('#manager_details').hide();
                                    $('#password_field').hide();
                                    $('#rule_field').hide();
                                    $('#password').val(''); // Clear password field
                                    $('#rule').val(''); // Clear rule field
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'خطأ',
                                text: 'هذا الموظف غير موجود و يرجى أدخال رقم ملف صحيح',
                                icon: 'error',
                                confirmButtonText: 'إلغاء',
                                confirmButtonColor: '#3085d6'
                            });
                            $('#mangered').val('');
                        }
                    }
                });
            } else {
                // Hide details if no manager ID
                $('#manager_details').hide();
                $('#password_field').hide();
                $('#rule_field').hide();
                $('#password').val('');
                $('#rule').val('');
            }
        }

        // Bind the blur event on the manager field to fetch the details
        $('#mangered').bind('blur', function() {
            var managerId = $(this).val();
            $('#password').val(''); // Clear previous input
            $('#rule').val(''); // Clear previous input
            fetchManagerDetails(managerId, true); // Fetch new details
        });
    </script>
    <script>
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
    </script>
   
@endsection
