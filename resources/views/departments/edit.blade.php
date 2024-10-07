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
        {{-- <div class="row " dir="rtl">
            <div class="container  col-11" style="background-color:transparent;">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item "><a href="{{ route('home') }}">الرئيسيه</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('departments.index') }}">القطاعات </a></li>
                        <li class="breadcrumb-item active" aria-current="page"> <a href="{{ route('departments.create') }}">
                                اضافة قطاع</a></li>
                    </ol>
                </nav>
            </div>
        </div> --}}
        <div class="row ">
            <div class="container welcome col-11">
                <p> تعديل أداره الرئيسيه </p>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="container  col-11 mt-3 p-0 ">
                <div class="container col-10 mt-5 mb-3 pb-5" style="border:0.5px solid #C7C7CC;">
                    <form action="{{ route('departments.update', $department->id) }}" method="POST"
                        enctype="multipart/form-data">
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
                            <div class="form-group col-md-10 mx-md-2">
                                <label for="sector">اختر القطاع </label>
                                <input type="text" name="name" id="name" class="form-control"
                                    value="{{ $department->sectors->name }}" disabled>
                                <input type="hidden" name="sector" id="sector" class="form-control"
                                    value="{{ $department->sectors->id }}">
                                @error('sector')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-10 mx-md-2">
                                <label for="name">أسم الأداره الرئيسية</label>
                                <input type="text" name="name" class="form-control" value="{{ $department->name }}">
                                @error('name')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-10 mx-md-2">
                                <label for="budget">ميزانية بدل حجز</label>
                                <input type="text" name="budget" class="form-control"
                                    value="{{ $department->reservation_allowance_amount }}">
                                @error('budget')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-10 mx-md-2">
                                <label for="">صلاحيه الحجز</label>
                                <div class="d-flex mt-3 " dir="rtl">
                                    <input type="checkbox" class="toggle-radio-buttons mx-2" value="1" id="part"
                                        @if ($department->reservation_allowance_type == 1 || $department->reservation_allowance_type == 3) checked @endif name="part[]">
                                    <label for="part"> حجز كلى</label><input type="checkbox"
                                        class="toggle-radio-buttons mx-2" value="2" id="part" name="part[]"
                                        @if ($department->reservation_allowance_type == 2 || $department->reservation_allowance_type == 3) checked @endif>
                                    <label for="part">حجز جزئى</label>
                                    @error('budget')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group col-md-10 mx-md-2" id="manager">
                                <label for="mangered">رقم هوية المدير</label>
                                <input type="text" name="manger" class="form-control" value="{{ $department->manger }}"
                                    required>

                                @error('manger')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-10 mx-md-2" id="password_field" style="display: none;">
                                <label for="password">كلمة المرور</label>
                                <input type="password" name="password" id="password" class="form-control">
                                @error('password')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group col-md-10 mx-md-2" id="manager_details">
                                <div class="col-12 div-info d-flex justify-content-between" style="direction: rtl">
                                    <div class="col-7">
                                        <div class="col-12 div-info-padding"><b>الرتبه : <span></span></b></div>
                                        <div class="col-12 div-info-padding"><b>الأقدميه : <span></span></b></div>

                                        <div class="col-12 div-info-padding"><b>المسمى الوظيفى: <span></span></b></div>
                                    </div>
                                    <div class="col-5">
                                        <div class="col-12 div-info-padding"><b>الأسم: <span></span></b></div>

                                        <div class="col-12 div-info-padding"><b>الهاتف: <span></span></b></div>
                                        <div class="col-12 div-info-padding"><b>الأيميل: <span></span></b></div>

                                    </div>
                                </div>
                            </div>


                        </div>
                        <div class="form-row mx-2 d-flex justify-content-center">

                            <div class="form-group col-md-10 mx-md-2">
                                <label for="description">الوصف </label>
                                <input type="text" name="description" class="form-control"
                                    value="{{ $department->description }}">
                                @error('description')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="input-group moftsh px-md-4 px-3 pt-3">
                                <label for="Civil_number" class="col-12"> أرقام الهوية</label>
                                <textarea class="form-control" name="Civil_number" id="Civil_number" style="height: 100px">
                                            @foreach ($employees as $employee)
{{ $employee->Civil_number }}
@endforeach
                                        </textarea>
                            </div>
                        </div>
                </div>
            </div>

        </div>
        <div class="container col-10 mt-5 mb-3 ">
            <div class="form-row col-10 " dir="ltr">
                <button class="btn-blue " type="submit">
                    اضافة </button>
            </div>
        </div>
        <br>

        </form>
        </div>



        </div>



        </div>
    </main>
    <script>
        $('.select2').select2({
            dir: "rtl"
        });
        $('#mangered').on('select2:select', function(e) {
            // alert('select');
            var managerId = $(this).val();
            fetchManagerDetails(managerId); // Fetch manager details based on the selected value
        });
        //  $(document).ready(function() {
        // Function to fetch and display manager details
        function fetchManagerDetails(managerId) {
            if (managerId) {
                // Make an AJAX request to fetch manager details
                $.ajax({
                    url: '/get-manager-details/' +
                        managerId, // Define your route to get manager details
                    type: 'GET',
                    success: function(data) {
                        // Populate the manager details in the div
                        $('#manager_details').find('span').eq(0).text(data.rank); // رتبه
                        $('#manager_details').find('span').eq(2).text(data.job_title); // مسمى وظيفي
                        $('#manager_details').find('span').eq(1).text(data.seniority); // أقدميه
                        $('#manager_details').find('span').eq(3).text(data.name); // أسم
                        $('#manager_details').find('span').eq(4).text(data.phone); // هاتف

                        // Show the manager details div
                        $('#manager_details').show();

                        // Check if the user is an employee and show password field
                        if (data.isEmployee) {
                            $('#password_field').show(); // Show the password field
                        } else {
                            $('#password_field').hide(); // Hide the password field
                        }
                    },
                    error: function() {
                        alert('Error fetching manager details.');
                    }
                });
            } else {
                // Hide the manager details if no manager is selected
                $('#manager_details').hide();
                $('#password_field').hide(); // Hide the password field
            }
        }

        // Hide the manager details and password field initially
        $('#manager_details').hide();
        $('#password_field').hide();

        // When the manager is selected or changed
        $('#mangered').change(function() {
            var managerId = $(this).val();
            fetchManagerDetails(managerId); // Fetch manager details based on the selected value
        });

        // On page load, check if there's already a selected manager
        var selectedManagerId = $('#mangered').val();
        if (selectedManagerId) {
            fetchManagerDetails(selectedManagerId); // Fetch details for the pre-selected manager
        }
        // });
    </script>
@endsection
