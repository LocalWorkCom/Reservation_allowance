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
@push('style')
@endpush
@section('title')
    القطاعات
@endsection
@section('content')
    <div class="row " dir="rtl">
        <div class="container  col-11" style="background-color:transparent; ">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item "><a href="/">الرئيسيه</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('sectors.index') }}">القطاعات</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> <a href=""> اضافة قطاع</a></li>
                </ol>
            </nav>
        </div>
    </div>
    <br>
    <form class="edit-grade-form" id="Qta3-form" action=" {{ route('sectors.store') }}" method="POST">
        @csrf
        <div class="row" dir="rtl">
            <div id="first-container" class="container moftsh col-11 py-3">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                {{-- @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif --}}
                <div class="form-row mx-2 mb-2">
                    <h3 class=" px-md-5 px-3">اضف قطاع</h3>
                    <div class="input-group moftsh px-md-5 px-3 pt-3">
                        <label class="pb-3" for="name">ادخل اسم القطاع</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}"
                            placeholder="قطاع واحد" required autocomplete="one-time-code" />
                        <span class="text-danger span-error" id="name-error"></span>

                    </div>
                </div>


                <div class="input-group moftsh px-md-5 px-3 pt-3" id="manager">
                    <label class="pb-3" for="mangered">رقم ملف المدير</label>
                    <input type="text" name="mangered" id="mangered" value="{{ old('mangered') }}" class="form-control"
                        autocomplete="one-time-code">
                    @error('mangered')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="input-group moftsh px-md-5 px-3 pt-3" id="email_field" style="display: none;"
                    @error('email') style="display: block;" @enderror>
                    <label class="pb-3 w-100" for="email"> الايميل</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control"
                        required>
                    @error('email')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="input-group moftsh px-md-5 px-3 pt-3" id="manager_details" style="display: none;">
                    <div class="col-12 div-info d-flex justify-content-between" style="direction: rtl">
                        <div class="col-7">
                            <div class="col-12 div-info-padding"><b>الرتبه : <span></span></b></div>
                            <div class="col-12 div-info-padding"><b>المسمى الوظيفى: <span></span></b></div>
                        </div>
                        <div class="col-5">
                            <div class="col-12 div-info-padding"><b>الأسم: <span></span></b></div>
                            <div class="col-12 div-info-padding"><b>الهاتف: <span></span></b></div>
                            <div class="col-12 div-info-padding" style="direction: rtl"><b>الأيميل: <span></span></b></div>
                        </div>
                    </div>
                </div>

                <div class="form-row mx-2 d-flex justify-content-center">

                    <div class="input-group moftsh px-md-5 px-3 pt-3">
                        <label for="Civil_number" class="col-12"> أرقام الملفات</label>
                        <textarea class="form-control" name="Civil_number" id="Civil_number" value="{{ old('Civil_number') }}"
                            style="height: 100px;background-color: #F8F8F8;border-radius: 10px !important;"></textarea>
                    </div>
                </div>
                {{-- {{   dd( old('budget_type'))}} --}}
                <div class="input-group moftsh px-md-5 px-3 pt-4">
                    <label for="" class="col-12">ميزانيه الحجز</label>
                    <div class="d-flex mt-3" dir="rtl">
                        <input type="radio" class="toggle-radio-buttons mx-2" name="budget_type"
                            {{ old('budget_type') == 1 ?? 'checked' }} value="1" id="notFree" style="height:30px;">
                        <label for="notFree" class="col-12">ميزانيه محدده</label>

                        <input type="radio" class="toggle-radio-buttons mx-2" name="budget_type"
                            {{ old('budget_type') == 2 ?? 'checked' }} value="2" id="free" style="height:30px;">
                        <label for="free" class="col-12">ميزانيه غير محدده</label>
                    </div>
                </div>
                @error('budget_type')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
                <div class="input-group moftsh px-md-5 px-3 pt-3" id="budgetField" style="display: none;">
                    <label class="d-flex pb-3" for="budget">ميزانية بدل حجز</label>
                    <input type="text" name="budget" class="form-control" value="{{ old('budget') }}"
                        autocomplete="one-time-code">
                    @error('budget')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="input-group moftsh px-md-5 px-3 pt-3 ">
                    <label for="" class="col-12">صلاحيه الحجز</label>
                    <div class="d-flex mt-3" dir="rtl">
                        <input type="checkbox" class="toggle-radio-buttons mx-2" value="1" id="fullBooking"
                            name="part[]" style="height:30px;">
                        <label for="fullBooking" class="col-12">حجز كلى</label>

                        <input type="checkbox" class="toggle-radio-buttons mx-2" value="2" id="partialBooking"
                            name="part[]" style="height:30px;">
                        <label for="partialBooking" class="col-12">حجز جزئى</label>

                        <input type="checkbox" class="toggle-radio-buttons mx-2" value="3" id="noBooking"
                            name="part[]" style="height:30px;">
                        <label for="noBooking" class="col-12">لا يوجد بدل حجز</label>
                    </div>

                </div>
                @error('part')
                    <div class="alert alert-danger moftsh px-md-5 px-3 pt-3" style="direction:rtl">{{ $message }}</div>
                @enderror
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
        $('.select2').select2({
            dir: "rtl"
        });
        // $(document).ready(function() {
        //     var selectedManagerId = $('#mangered').val();
        //     let validationType = @json(session('validation_type', null));

        //     if (selectedManagerId) {
        //         $('#email_field').show();
        //         fetchManagerDetails(selectedManagerId, false);
        //         $('#manager_details').show();


        //     } else {
        //         $('#manager_details').hide();
        //         $('#email_field').hide();
        //     }
        // });
        function fetchManagerDetails(managerId, skipDepartmentCheck = false) {
            var oldManagerId = $('#mangered').val();
            var oldEmail = $('#email').val();
            if (managerId) {
                sector = null;
                $.ajax({
                    url: '/get-manager-sector-details/' + managerId + '/' + sector + '?skipDepartmentCheck=' +
                        skipDepartmentCheck,
                    type: 'GET',
                    success: function(data) {
                        $('#manager_details').find('span').eq(0).text(data.rank);
                        // $('#manager_details').find('span').eq(1).text(data.seniority);
                        $('#manager_details').find('span').eq(1).text(data.job_title);
                        $('#manager_details').find('span').eq(2).text(data.name);
                        $('#manager_details').find('span').eq(3).text(data.phone);
                        $('#manager_details').find('span').eq(4).text(data.email);

                        $('#manager_details').show();
                        if (data.email) {
                            $('#email_field').show();
                            if (data.email === 'لا يوجد بريد الكتروني') {
                                $('#email').val('');

                            } else {
                                $('#email').val(data.email);

                            }
                        } else {
                            // $('#email_field').hide();
                            $('#email').val(data.email);

                        }
                    },
                    error: function(xhr) {
                        if (xhr) {
                            fetchManagerDetails(managerId, false);
                            $('#email_field').show();
                            $('#email').show();
                            $('#email').val(oldEmail);

                            // if (xhr.error) {

                            // } else {
                            //     $('#email').val(oldEmail);

                            // }
                            $('#manager_details').show();
                            // Populate manager details
                            $('#manager_details').find('span').eq(0).text(result.rank);
                            $('#manager_details').find('span').eq(1).text(result.job_title);
                            $('#manager_details').find('span').eq(2).text(result.name);
                            $('#manager_details').find('span').eq(3).text(result.phone);
                            $('#manager_details').find('span').eq(4).text(result.email);
                            // Swal.fire({
                            //     title: 'تحذير',
                            //     text: xhr.responseJSON.error || 'عفوا هذا المستخدم غير موجود',
                            //     icon: 'warning',
                            //     showCancelButton: true,
                            //     confirmButtonText: 'نعم, استمر',
                            //     cancelButtonText: 'لا',
                            //     confirmButtonColor: '#3085d6',
                            //     cancelButtonColor: '#d33'
                            // }).then((result) => {
                            //     if (result.isConfirmed) {
                            //         fetchManagerDetails(managerId, false);
                            //         $('#email_field').show();
                            //         $('#email').show();
                            //         if (data.email === 'لا يوجد بريد الكتروني') {
                            //             $('#email').val('');

                            //         } else {
                            //             $('#email').val(data.email);

                            //         }
                            //         $('#manager_details').show();
                            //         // Populate manager details
                            //         $('#manager_details').find('span').eq(0).text(result.rank);
                            //         $('#manager_details').find('span').eq(1).text(result.job_title);
                            //         $('#manager_details').find('span').eq(2).text(result.name);
                            //         $('#manager_details').find('span').eq(3).text(result.phone);
                            //         $('#manager_details').find('span').eq(4).text(result.email);

                            //     } else {
                            //         // Hide details if user cancels
                            //         $('#mangered').val(''); // Clear manager input field
                            //         $('#manager_details').hide();
                            //         $('#email_field').hide();
                            //         $('#email').val(''); // Clear password field
                            //     }
                            // });
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
                $('#manager_details').hide();
                $('#email_field').hide();
                $('#email').val('');
            }
        }
        $('#manager_details').hide();
        $('#email_field').hide();
        $('#mangered').bind('blur', function() {
            var managerId = $(this).val();
            $('#email').val('');

            fetchManagerDetails(managerId, true);
        });
        var selectedManagerId = $('#mangered').val();
        if (selectedManagerId) {
            fetchManagerDetails(selectedManagerId, true);
        }
    </script>
    <script>
        // Select the checkboxes by their IDs
        const fullBooking = document.getElementById("fullBooking");
        const partialBooking = document.getElementById("partialBooking");
        const noBooking = document.getElementById("noBooking");

        // Add an onchange event listener for the "noBooking" checkbox
        noBooking.addEventListener("change", function() {
            if (noBooking.checked) {
                // Uncheck the other checkboxes if "noBooking" is checked
                fullBooking.checked = false;
                partialBooking.checked = false;
            }
        });

        // Add event listeners for "fullBooking" and "partialBooking" to uncheck "noBooking" if either is checked
        fullBooking.addEventListener("change", function() {
            if (fullBooking.checked) {
                noBooking.checked = false;
            }
        });

        partialBooking.addEventListener("change", function() {
            if (partialBooking.checked) {
                noBooking.checked = false;
            }
        });
    </script>

    <script>
        // Add event listeners for the radio buttons
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

        });
    </script>
@endsection
