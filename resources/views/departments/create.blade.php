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
    أضافة أداره رئيسية
@endsection
@push('style')
@endpush
@section('content')
    <main>
        <div class="row " dir="rtl">
            <div class="container  col-11" style="background-color:transparent;">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item "><a href="{{ route('home') }}">الرئيسيه</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('departments.index', $sectors->uuid) }}">الادارات</a>
                        </li>

                        <li class="breadcrumb-item active" aria-current="page"> <a href="">
                                اضافة ادارة</a></li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row ">
            <div class="container welcome col-11">
                <p> أضافة أداره رئيسية</p>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="container  col-11 mt-3 py-4 ">
                <div class="container col-11" style="border:0.5px solid #C7C7CC;">
                    <form action="{{ route('departments.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger">
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
                                    value="{{ $sectors->name }}" disabled>
                                <input type="hidden" name="sector" id="sector" class="form-control"
                                    value="{{ $sectors->id }}">
                                @error('sector')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-12 mx-md-2">
                                <label for="name">أسم الأداره الرئيسية</label>
                                <input type="text" name="name" class="form-control" autocomplete="one-time-code"
                                    value="{{ old('name') }}">
                                @error('name')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group col-md-12 mx-md-2" id="manager">
                                <label for="mangered">رقم ملف المدير</label>
                                <input type="text" name="mangered" id="mangered" class="form-control"
                                    autocomplete="one-time-code" value="{{ old('mangered') }}">


                                @error('mangered')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-12 mx-md-2" id="email_field" style="display: none;">
                                <label class="pb-3 w-100" for="email"> الايميل</label>
                                <input type="email" name="email" id="email" class="form-control"
                                    value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-12 mx-md-2" id="manager_details">
                                <div class="col-12 div-info d-flex justify-content-between" style="direction: rtl">
                                    <div class="col-7">
                                        <div class="col-12 div-info-padding"><b>الرتبه : <span></span></b></div>
                                        <div class="col-12 div-info-padding"><b>المسمى الوظيفى: <span></span></b></div>
                                    </div>
                                    <div class="col-5">
                                        <div class="col-12 div-info-padding"><b>الأسم: <span></span></b></div>
                                        <div class="col-12 div-info-padding"><b>الهاتف: <span></span></b></div>
                                        <div class="col-12 div-info-padding" style="direction: rtl"><b>الأيميل:
                                                <span></span></b></div>

                                    </div>
                                </div>
                            </div>

                            <div class="form-group moftsh col-md-12 mx-md-2">
                                <label for="description">الوصف </label>
                                <input type="text" name="description" class="form-control" autocomplete="one-time-code"
                                    value="{{ old('description') }}">
                                @error('description')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group moftsh col-md-12 mx-md-2">
                                <label for="file_number" class="col-12"> أرقام الملفات</label>
                                <textarea class="form-control" name="file_number" id="file_number" style="height: 100px"></textarea>
                            </div>
                            <div class="form-group col-md-12 pt-4 mx-md-2" dir="rtl">
                                <h4 class="mb-3 d-flex justify-content-start">صلاحيه الحجز</h4>
                                <div class="d-flex  mt-3">
                                    <input type="checkbox" class="toggle-radio-buttons mx-2" value="1"
                                        id="fullBooking" name="part[]">
                                    <label for="fullBooking"> حجز كلى</label>
                                    <input type="checkbox" class="toggle-radio-buttons mx-2" value="2"
                                        id="partialBooking" name="part[]">
                                    <label for="partialBooking">حجز جزئى</label>
                                    <input type="checkbox" class="toggle-radio-buttons mx-2" value="3"
                                        id="noBooking" name="part[]">
                                    <label for="noBooking">لا يوجد بدل حجز</label>
                                    {{-- @error('part')
                                    <div class="alert alert-danger">{{ $message }}
                            </div>
                            @enderror --}}
                                </div>
                                <div class="form-group col-md-12 pt-4">

                                    <h4 class="mb-3 d-flex justify-content-start">ميزانيه الحجز</h4>


                                    <div class="d-flex mt-3">
                                        <label for="notFree" class="d-flex align-items-center">
                                            <input type="radio" class="toggle-radio-buttons " name="budget_type"
                                                value="1" id="notFree" style="height:20px;"> ميزانيه محدده

                                        </label>

                                        <label for="free" class="d-flex align-items-center">
                                            <input type="radio" class="toggle-radio-buttons me-2" name="budget_type"
                                                value="2" id="free" style="height:20px;">ميزانيه غير محدده

                                        </label>
                                    </div>
                                </div>
                                <div class="form-group col-md-12 " id="budgetField" style="display: none;">

                                    <label class="d-flex justify-content-start pb-3" for="budget"
                                        class="col-12">ميزانية بدل
                                        حجز</label>
                                    <input type="text" name="budget" class="form-control"
                                        value="{{ old('budget') }}" autocomplete="one-time-code">
                                    @error('budget')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                        </div>
                        <div class="container col-12 mt-5 ">
                            <button class="btn-blue " type="submit"> اضافة </button>

                        </div>
                        <br>
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

        function fetchManagerDetails(managerId) {
            if (managerId) {
                var sectorId = $('#sector').val();

                $.ajax({
                    url: '/get-manager-details/' + managerId,
                    type: 'GET',
                    data: {
                        sector_id: sectorId
                    }, // Send sector_id to the backend
                    success: function(data) {
                        $('#manager_details').find('span').eq(0).text(data.rank);
                        $('#manager_details').find('span').eq(1).text(data.job_title);
                        $('#manager_details').find('span').eq(2).text(data.name);
                        $('#manager_details').find('span').eq(3).text(data.phone);
                        $('#manager_details').find('span').eq(4).text(data.email);
                        $('#manager_details').show();

                        // Show password and rule fields for employees
                        if (data.email) {
                            $('#email_field').show();
                            if (data.email === 'لا يوجد بريد الكتروني') {
                                $('#email').val('');

                            } else {
                                $('#email').val(data.email);

                            }
                        } else {
                            $('#email_field').hide();
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
                                    console.log('Transfer confirmed');
                                } else {
                                    //clear the manager input field
                                    $('#mangered').val(''); // Clear the input field
                                    $('#manager_details').hide(); // Hide the manager details
                                    $('#email_field').hide();
                                }
                            });
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status == 405) {
                            // fetchManagerDetails(managerId, false);
                            // $('#email_field').show();
                            // $('#email').show();
                            // $('#email').val(oldEmail);

                            // // if (xhr.error) {

                            // // } else {
                            // //     $('#email').val(oldEmail);

                            // // }
                            // $('#manager_details').show();
                            // // Populate manager details
                            // $('#manager_details').find('span').eq(0).text(result.rank);
                            // $('#manager_details').find('span').eq(1).text(result.job_title);
                            // $('#manager_details').find('span').eq(2).text(result.name);
                            // $('#manager_details').find('span').eq(3).text(result.phone);
                            // $('#manager_details').find('span').eq(4).text(result.email);
                            Swal.fire({
                                title: 'تحذير',
                                text: xhr.responseJSON.error,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'نعم, استمر',
                                cancelButtonText: 'لا',
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    fetchManagerDetails(managerId, false);
                                    $('#email_field').show();
                                    $('#email').show();
                                    if (data.email === 'لا يوجد بريد الكتروني') {
                                        $('#email').val('');

                                    } else {
                                        $('#email').val(data.email);

                                    }
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
                                confirmButtonColor: '#3085d6'
                            });
                            $('#mangered').val('');
                        }
                    }
                });
            } else {
                // Reset the manager details if no manager ID is provided
                $('#manager_details').hide();
                $('#email_field').hide();
                $('#email').val('');
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
        // Select the checkboxes by their IDs
        const fullBooking = document.getElementById("fullBooking");
        const partialBooking = document.getElementById("partialBooking");
        const noBooking = document.getElementById("noBooking");
        noBooking.addEventListener("change", function() {
            if (noBooking.checked) {
                fullBooking.checked = false;
                partialBooking.checked = false;
            }
        });
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
