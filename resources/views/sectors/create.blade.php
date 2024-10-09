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
        <div class="container  col-11" style="background-color:transparent;">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item "><a href="/">الرئيسيه</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('sectors.index') }}">القطاعات</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> <a href=""> اضافة قطاع</a></li>
                </ol>
            </nav>
        </div>
    </div>
    {{-- <div class="row ">
        <div class="container welcome col-11">
            <p> القطــــاعات </p>
        </div>
    </div> --}}
    {{-- {{ dd($governments) }} --}}
    <br>
    <form class="edit-grade-form" id="Qta3-form" action=" {{ route('sectors.store') }}" method="POST">
        @csrf
        <div class="row" dir="rtl">
            <div id="first-container" class="container moftsh col-11 mt-3 p-0 pb-3">
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
                <div class="form-row mx-2 mb-2">
                    <h3 class="pt-3 px-md-5 px-3">اضف قطاع</h3>
                    <div class="input-group moftsh px-md-5 px-3 pt-3">
                        <label class="pb-3" for="name">ادخل اسم القطاع</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="قطاع واحد"
                            required />
                        <span class="text-danger span-error" id="name-error"></span>

                    </div>
                </div>
                <div class="input-group moftsh px-md-5 px-3 pt-3">
                    <label class="pb-3" for="budget">ميزانية بدل حجز</label>
                    <input type="text" name="budget" class="form-control" value="{{ old('budget') }}">
                    @error('budget')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="input-group moftsh px-md-5 px-3 pt-3" id="manager">
                    <label class="pb-3" for="mangered">رقم هوية المدير</label>
                    <input type="text" name="mangered" id="mangered" class="form-control" value="{{ old('mangered') }}">
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
                    <label class="pb-3" for="rule">القانون</label>
                    <select name="rule" id="rule" class="form-control">
                        <option value="">اختار القانون</option>
                        @foreach ($rules as $rule)
                            <option value="{{ $rule->id }}">{{ $rule->name }}</option>
                        @endforeach
                    </select>
                    @error('rule')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="input-group moftsh px-md-5 px-3 pt-3" id="manager_details" style="display: none;">
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

                <div class="form-row mx-2 d-flex justify-content-center">

                    <div class="input-group moftsh px-md-4 px-3 pt-3">
                        <label for="Civil_number" class="col-12"> أرقام الهوية</label>
                        <textarea class="form-control" name="Civil_number" id="Civil_number" style="height: 100px"></textarea>
                    </div>
                </div>
                <div class="input-group moftsh px-md-5 px-3 pt-3 ">
                    <label for="" class="col-12">صلاحيه الحجز</label>
                    <div class="d-flex mt-3 " dir="rtl">
                        <input type="checkbox" class="toggle-radio-buttons mx-2" value="1" id="part"
                            name="part[]" style="height:30px;">
                        <label for="part" class="col-12"> حجز كلى</label>
                        <input type="checkbox" class="toggle-radio-buttons mx-2" value="2" id="part"
                            name="part[]" style="height:30px;">
                        <label for="part" class="col-12">حجز جزئى</label>
                        {{-- @error('part')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror --}}
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
        $('.select2').select2({
            dir: "rtl"
        });

        function fetchManagerDetails(managerId, skipDepartmentCheck = true) {
            if (managerId) {
                sector = null;
                $.ajax({
                    url: '/get-manager-sector-details/' + managerId + '/' + sector + (skipDepartmentCheck ?
                        '?check_department=false' :
                        ''),
                    type: 'GET',
                    success: function(data) {
                        $('#manager_details').find('span').eq(0).text(data.rank);
                        $('#manager_details').find('span').eq(1).text(data.seniority);
                        $('#manager_details').find('span').eq(2).text(data.job_title);
                        $('#manager_details').find('span').eq(3).text(data.name);
                        $('#manager_details').find('span').eq(4).text(data.phone);
                        $('#manager_details').show();
                        if (data.isEmployee) {
                            $('#password_field').show();
                            $('#rule_field').show();
                        } else {
                            $('#password_field').hide();
                            $('#rule_field').hide();
                            $('#password').val('');
                            $('#rule').val('');
                        }
                    },
                    error: function(xhr) {
                        console.log(xhr);
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
                                // User confirmed
                                // You may want to handle confirmation logic here
                            } else {
                                // User clicked "إلغاء", clear the input field
                                $('#mangered').val('');
                                $('#manager_details').hide();
                                $('#password_field').hide();
                                $('#rule_field').hide();
                                $('#password').val('');
                                $('#rule').val('');
                            }
                        });
                    }
                });
            } else {
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
        $('#mangered').on('input', function() {
            var managerId = $(this).val();
            $('#password').val('');
            $('#rule').val('');
            fetchManagerDetails(managerId, true);
        });
        var selectedManagerId = $('#mangered').val();
        if (selectedManagerId) {
            fetchManagerDetails(selectedManagerId, true);
        }
    </script>
@endsection
