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
    أضافة أداره فرعية
@endsection
@section('content')
    <main>
        <div class="row " dir="rtl">
            <div class="container  col-11" style="background-color:transparent;">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item "><a href="{{ route('home') }}">الرئيسيه</a></li>
                        @if ($department->parent_id)
                            <li class="breadcrumb-item"><a
                                    href="{{ route('sub_departments.index', ['id' => $department->parent_id]) }}">
                                    {{ $department->name }}
                                </a></li>
                        @else
                            <li class="breadcrumb-item"><a
                                    href="{{ route('departments.index', ['id' => $department->id]) }}">
                                    {{ $department->name }}
                                </a></li>
                        @endif
                        <li class="breadcrumb-item active" aria-current="page"> <a href="">
                                اضافة ادارة فرعية</a></li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row ">
            <div class="container welcome col-11">
                <p> اضافه أداره </p>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="container  col-11 mt-3 p-0 ">
                <div class="container col-10 mt-5 mb-3 pb-5" style="border:0.5px solid #C7C7CC;">
                    <form action="{{ route('sub_departments.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
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
                                <label for="sector">أسم القطاع </label>
                                {{-- <select name="sector" id="sector" class="form-control " required>
                                    <option value="">اختر القطاع </option>
                                    @foreach ($sectors as $sector)
                                        <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                                    @endforeach
                                </select> --}}
                                <input type="hidden" name="parent" value="{{ $department->id }}">
                                <input type="hidden" name="sector" value="{{ $department->sector_id }}">

                                <input type="text" class="form-control" name="sectors" id="sector"
                                    value="{{ $department->sectors ? $department->sectors->name : 'No sector available' }}"
                                    disabled>
                                @error('sector')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group col-md-10 mx-md-2">
                                <label for="parent_department">اسم الادارة الرئيسية</label>
                                @php
                                    $parentDepartment = App\Models\departements::find($department->id);
                                @endphp
                                <input type="hidden" name="parent" value="{{ $department->id }}">
                                <input type="text" class="form-control" name="parent_department" id="parent_department"
                                    value="{{ $parentDepartment ? $parentDepartment->name : 'No Parent Department' }}"
                                    disabled>
                                @error('parent_department')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-10 mx-md-2">
                                <label for="name">أسم الأداره الفرعية</label>
                                <input type="text" name="name" class="form-control" autocomplete="one-time-code"
                                    value="{{ old('name') }}">
                                @error('name')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-10 mx-md-2">
                                <label for="budget">ميزانية بدل حجز</label>
                                <input type="text" name="budget" class="form-control" autocomplete="one-time-code"
                                    value="{{ old('budget') }}">
                                @error('budget')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-10 mx-md-2">
                                <label for="">صلاحيه الحجز</label>
                                <div class="d-flex mt-3 " dir="rtl">
                                    <input type="checkbox" class="toggle-radio-buttons mx-2" value="1" id="part"
                                        name="part[]">
                                    <label for="part"> حجز كلى</label><input type="checkbox"
                                        class="toggle-radio-buttons mx-2" value="2" id="part" name="part[]">
                                    <label for="part">حجز جزئى</label>
                                    @error('part')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group col-md-10 mx-md-2" id="manager">
                                <label for="mangered">رقم ملف المدير</label>
                                <input type="text" name="mangered" id="mangered" class="form-control"
                                    autocomplete="one-time-code" value="{{ old('mangered') }}">

                                @error('mangered')
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
                                    autocomplete="one-time-code" value="{{ old('description') }}">
                                @error('description')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="input-group moftsh col-md-10 mx-md-2">
                                <label for="file_number" class="col-12"> أرقام الملفات</label>
                                <textarea class="form-control" name="file_number" id="file_number" style="height: 100px"></textarea>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $('.select2').select2({
            dir: "rtl"
        });

        function fetchManagerDetails(managerId) {
            console.log('Manager ID:', managerId);

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
                        $('#manager_details').find('span').eq(1).text(data.seniority);
                        $('#manager_details').find('span').eq(2).text(data.job_title);
                        $('#manager_details').find('span').eq(3).text(data.name);
                        $('#manager_details').find('span').eq(4).text(data.phone);
                        $('#manager_details').find('span').eq(5).text(data.email);
                        $('#manager_details').show();

                        // Show password and rule fields for employees
                        if (data.isEmployee) {
                            $('#password_field').show();
                            $('#rule_field').show();
                        } else {
                            $('#password_field').hide();
                            $('#rule_field').hide();
                            $('#password').val('');
                            $('#rule').val('');
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
                                    // Populate manager details if no transfer is needed

                                    console.log('Transfer confirmed');
                                } else {
                                    // Handle cancel action: clear the manager input field
                                    $('#mangered').val(''); // Clear the input field
                                    $('#manager_details').hide(); // Hide the manager details
                                    $('#password_field').hide();
                                    $('#rule_field').hide();
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
                                $('#password_field').hide();
                                $('#rule_field').hide();
                                $('#password').val('');
                                $('#rule').val('');
                            });
                        }
                    }
                });
            } else {
                // Reset the manager details if no manager ID is provided
                $('#manager_details').hide();
                $('#password_field').hide();
                $('#rule_field').hide();
                $('#password').val('');
                $('#rule').val('');
            }
        }

        $('#manager_details').hide();
        $('#password_field').hide();
        $('#rule_field').hide();

        // Use 'blur' event to trigger the check when the input field loses focus
        $('#mangered').on('blur', function() {
            var managerId = $(this).val();
            $('#password').val('');
            $('#rule').val('');
            fetchManagerDetails(managerId);
        });

        var selectedManagerId = $('#mangered').val();
        if (selectedManagerId) {
            fetchManagerDetails(selectedManagerId);
        }
    </script>
@endsection
