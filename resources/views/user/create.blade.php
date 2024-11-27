@extends('layout.main')
@section('title')
    اضافة
@endsection
@section('content')
    <div class="row " dir="rtl">
        <div class="container  col-11" style="background-color:transparent;">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item "><a href="/">الرئيسيه</a></li>

                    @if (Auth::user()->rule_id == 2)
                        <li class="breadcrumb-item"><a href="{{ route('user.employees', 'employee') }}">موظفين الوزارة</a>
                        </li>
                    @endif
                    @if (Auth::user()->rule_id != 2)
                        <li class="breadcrumb-item"><a href="{{ route('user.employees', 'employee') }}">موظفين القوة</a>
                        </li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page"> <a href=""> اضافة </a></li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="row ">
        <div class="container welcome col-11">
            @if (Auth::user()->rule_id == 2)
                <p>موظفين الوزارة</p>
            @endif
            @if (Auth::user()->rule_id != 2)
                <p>موظفين القوة</p>
            @endif
        </div>
    </div>
    <div class="row">
        <div class="container  col-11 mt-5 p-0 ">
            <div class="container col-10 mt-5 mb-4 pb-4" style="border:0.5px solid #C7C7CC;">

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
                <div class="">
                    <form action="{{ route('user.store') }}" method="post" class="text-right"
                        enctype="multipart/form-data">
                        @csrf

                        <div class="form-row pt-5 pb-3 d-flex justify-content-around flex-row-reverse"
                            style="background-color:#f5f8fd; border-bottom:0.1px solid lightgray;">
                            <div class="form-group d-flex  justify-content-center col-md-5 mx-2 pb-2">
                                <div class="radio-btns mx-md-4 ">
                                    <input type="radio" class="form-check-input" id="male" name="gender"
                                        value="man" style="height:20px; width:20px;" checked>
                                    <label class="form-check-label mx-2" for="male">ذكر</label>
                                </div>
                                <div class="radio-btns mx-md-4 ">
                                    <input type="radio" class="form-check-input" id="female" name="gender"
                                        value="female" style="height:20px; width:20px;">
                                    <label class="form-check-label mx-2" for="female">انثى</label>
                                </div>
                                <label for="input44 " class="input44-none mx-3">الفئة </label>

                            </div>

                            <div class="form-group d-flex justify-content-center col-md-5 mx-2 pb-2">
                                <!-- Violation type radio buttons -->
                                @foreach ($violationTypeName as $key => $violation)
                                    {{-- {{ dd($violationTypeName) }} --}}

                                    <div class="radio-btns" style="margin-left: 1.5rem; margin-right: 1.5rem;">
                                        <input type="radio" class="form-check-input" id="police_{{ $key }}"
                                            name="type_military" value="{{ $violation->id }}"
                                            style="height: 20px; width: 20px;"
                                            {{ old('type_military', 'police') == $violation->id ? 'checked' : '' }}>
                                        <label class="form-check-label mx-2" for="police_{{ $key }}"
                                            style="margin-left: 0.5rem;">
                                            {{ $violation->name }}
                                        </label>
                                    </div>
                                @endforeach

                                <!-- Label for the radio group -->
                                <label for="type_military" style="margin-left: 1.5rem; margin-right: 1.5rem;">
                                    نوع العسكرى
                                </label>
                            </div>

                            {{-- <div class="form-group d-flex  justify-content-end col-md-5 mx-2">
                            <div class="radio-btns mx-md-4 ">
                                <input type="radio" class="form-check-input" id="solder" name="solderORcivil"
                                    value="military" style="height:20px; width:20px;">
                                <label class="form-check-label mx-md-2" for="solder">عسكرى</label>
                            </div>
                            <div class="radio-btns mx-md-4">
                                <input type="radio" class="form-check-input" id="civil" name="solderORcivil"
                                    value="civil" style="height:20px; width:20px;" checked>
                                <label class="form-check-label mx-md-2" for="civil">مدنى</label>
                            </div>
                            <label for="input44" class="mx-3">التصنيف</label>
                            </div> --}}
                        </div>

                        {{-- <div class="form-group col-md-10 mx-2 type_military_id " id="type_military_id">
                            <div class="d-flex justify-content-end">
                                <div class="radio-btns mx-md-4 ">
                                    <input type="radio" class="form-check-input" id="police" name="type_military"
                                        value="police" style="height:20px; width:20px;">
                                    <label class="form-check-label mx-2" for="police">فرد</label>
                                </div>
                                <label for="type_military">نوع العسكرى</label>
                            </div>
                        </div> --}}

                        <div class="form-row mx-md-3 d-flex justify-content-center flex-row-reverse">
                            <div class="form-group col-md-5 mx-2">
                                <label for="nameus"> <i class="fa-solid fa-asterisk"
                                        style="color:red; font-size:10px;"></i>
                                    الاسم</label>
                                <input type="text" id="nameus" name="name" class="form-control" placeholder="الاسم"
                                    value="{{ old('name') }}">
                            </div>
                            <div class="form-group col-md-5 mx-2">
                                <label for="input2">
                                    البريد الالكتروني</label>
                                <input type="text" id="input2" name="email" class="form-control"
                                    placeholder=" البريد الالكترونى" value="{{ old('email') }}">
                            </div>

                            <div class="form-group col-md-5 mx-2">
                                <label for="input4"> <i class="fa-solid fa-asterisk"
                                        style="color:red; font-size:10px;"></i> رقم
                                    الهاتف</label>
                                <input type="text" id="input4" name="phone" class="form-control"
                                    placeholder=" رقم الهاتف" value="{{ old('phone') }}">
                            </div>
                            <div class="form-group col-md-5 mx-2">
                                <label for="region"> المنطقة</label>

                                <select id="region" name="region" class="form-control select2" placeholder="المنطقة">
                                    <option selected disabled>اختار من القائمة</option>
                                    @foreach ($area as $item)
                                        <option value="{{ $item->id }}"
                                            {{ old('region') == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <div class="form-row mx-md-3 d-flex justify-content-center flex-row-reverse">
                            <div class="form-group col-md-5 mx-2">
                                <label for="input44">العنوان</label>
                                <textarea id="input44" name="address_1" class="form-control" placeholder="  العنوان"
                                    value="{{ old('address_1') }}">{{ old('address_1') }}</textarea>
                            </div>
                            <div class="form-group col-md-5 mx-2">
                                <label for="input11"> <i class="fa-solid fa-asterisk"
                                        style="color:red; font-size:10px;"></i>
                                    رقم المدنى</label>
                                <input type="text" id="input11" name="Civil_number" class="form-control"
                                    placeholder="رقم المدنى" value="{{ old('Civil_number') }}">
                            </div>


                        </div>


                        <div class="form-row  mx-md-3 d-flex justify-content-center flex-row-reverse">
                            <div class="form-group col-md-5 mx-2">
                                <label for="input9"> المسمي الوظيفي</label>
                                <input type="text" id="input9" name="job_title" class="form-control"
                                    placeholder="المسمي الوظيفي" value="{{ old('job_title') }}">
                            </div>
                            <div class="form-group col-md-5 mx-2">
                                <label for="country_select">الجنسية</label>
                                <select id="country_select" name="nationality" class="form-control select2">
                                    <option selected disabled>اختار من القائمة</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}"
                                            {{ old('nationality') == $country->id ? 'selected' : '' }}>
                                            {{ $country->country_name_ar }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <div class="form-row  mx-md-3 d-flex justify-content-center flex-row-reverse">

                            <div class="form-group col-md-5 mx-2" id="military_number_id">

                                <label for="input6">
                                    رقم العسكرى</label>
                                <input type="text" id="input6" name="military_number" class="form-control"
                                    placeholder="رقم العسكرى" value="{{ old('military_number') }}">
                            </div>
                            <div class="form-group col-md-5 mx-2" id="input12Div">

                                <label for="input12"><i class="fa-solid fa-asterisk"
                                        style="color:red; font-size:10px;"></i>
                                    رقم الملف
                                </label>
                                <input type="text" id="input12" name="file_number" class="form-control"
                                    placeholder="رقم الملف" value="{{ old('file_number') }}">
                            </div>
                            <div class="form-group col-md-10 mx-2">
                                <label for="input13">
                                    <i class="fa-solid fa-asterisk" style="color:red; font-size:10px;"></i> هل يمكن لهذا
                                    الموظف أن يكون مستخدم؟
                                </label>
                                <select id="input13" name="flag" class="form-control">
                                    <option value="">اختر النوع</option>
                                    <option value="user" @if (old('flag') == 'user') selected @endif>نعم</option>
                                    <option value="employee" @if (old('flag') == 'employee') selected @endif>لا</option>
                                </select>
                            </div>
                            <div class="form-row mx-md-3 d-flex justify-content-center {{ old('flag') === 'user' ? '' : 'd-none' }} flex-row-reverse col-12"
                                id="additionalFields">
                                <div class="form-group col-md-5 mx-2">
                                    <label for="input66">
                                        <i class="fa-solid fa-asterisk" style="color:red; font-size:10px;"></i> الباسورد
                                    </label>
                                    <div class="password-container">
                                        <input type="password" id="input3" name="password" class="form-control"
                                            value="{{ old('password') }}" placeholder="الباسورد">
                                        <label class="toggle-password" onclick="togglePasswordVisibility()">
                                            <i id="toggleIcon" class="fa fa-eye eye-icon"></i>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group col-md-5 mx-2">
                                    <label for="input77">
                                        <i class="fa-solid fa-asterisk" style="color:red; font-size:10px;"></i> المهام
                                    </label>
                                    <select id="input77" name="rule_id" class="form-control select2"
                                        placeholder="المهام">
                                        <option disabled>اختار من القائمة</option>
                                        @foreach ($rule as $item)
                                            <option value="{{ $item->id }}"
                                                {{ old('rule_id') == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>


                        <div class="form-row  mx-md-3 d-flex justify-content-center flex-row-reverse">


                            <div class="form-group col-md-5 mx-2">
                                <label for="sector">القطاع</label>
                                <select id="sector" name="sector" class="form-control" placeholder="القطاع">
                                    <option value="{{ null }}">لا يوجد قسم محدد</option>
                                    @foreach ($sectors as $sector)
                                        <option value="{{ $sector->id }}"
                                            {{ old('sector') == $sector->id ? 'selected' : '' }}>
                                            {{ $sector->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-5 mx-2">
                                <label for="department_id">الادارة</label>
                                <select id="department_id" name="department_id" class="form-control"
                                    placeholder="الادارة">
                                    <option value="{{ null }}">لا يوجد قسم محدد</option>
                                    {{-- @foreach ($alldepartment as $item)
                                        <option value="{{ $item->id }}"
                                            {{ old('department_id') == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach --}}
                                </select>
                            </div>
                        </div>
                        <div class="form-row mx-md-3  d-flex justify-content-center flex-row-reverse">

                            <div class="form-group col-md-5 mx-2">
                                <label for="gradeSelect"><i class="fa-solid fa-asterisk"
                                        style="color:red; font-size:10px;"></i>
                                    الرتبة</label>
                                <select id="gradeSelect" name="grade_id" class="form-control select2" required>
                                    <option selected disabled>اختار من القائمة</option>
                                    @foreach ($grades as $item)
                                        <option value="{{ $item->id }}"
                                            {{ old('grade_id') == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-row mx-md-3  d-flex justify-content-center flex-row-reverse">
                            <div class="form-group col-md-5 mx-2">
                                <label for="input19">تاريخ الميلاد</label>
                                <input type="date" id="input19" name="date_of_birth" class="form-control"
                                    placeholder="تاريخ الميلاد" value="{{ old('date_of_birth') }}">
                            </div>
                            <div class="form-group col-md-5 mx-2">
                                <label for="input20">تاريخ الالتحاق</label>
                                <input type="date" id="input20" name="joining_date" class="form-control"
                                    placeholder="تاريخ الالتحاق" value="{{ old('joining_date') }}">
                            </div>
                        </div>

                        <div class="form-row mx-md-2  d-flex justify-content-center flex-row-reverse">
                            <div class="form-group col-md-10">
                                <label for="input5"> الملاحظات</label>
                                <textarea type="text" id="input5" name="description" class="form-control" placeholder="الملاحظات"
                                    rows="3" value="{{ old('description') }}">{{ old('description') }}</textarea>
                            </div>
                        </div>

                        <div class="form-row mx-md-2  d-flex justify-content-center flex-row-reverse">
                            <div class="form-group col-md-10">
                                <label for="input23">الصورة</label>
                                <input type="file" class="form-control" name="image" id="input23"
                                    placeholder="الصورة">
                                @if ($errors->has('image'))
                                    <div class="text-danger">
                                        {{ $errors->first('image') }}
                                    </div>
                                @elseif(old('image'))
                                    <img src="{{ asset('storage/' . old('image')) }}" alt="Uploaded Image"
                                        width="100">
                                @endif
                            </div>
                        </div>

                </div>



                <div class="container col-10 mt-3 mb-3 ">
                    <div class="form-row col-10 " dir="ltr">
                        <button class="btn-blue " type="submit">
                            اضافة </button>
                    </div>
                </div>
                <br>
                </form>



            </div>

        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const flagSelect = document.getElementById('input13'); // Flag selector
                const ruleSelect = document.getElementById('input77'); // Rule selector
                const departmentSelect = document.getElementById('department_id'); // Department
                const sectorSelect = document.getElementById('sector'); // Sector
                const additionalFields = document.getElementById('additionalFields'); // Additional fields div

                function handleDynamicFields() {
                    const flagValue = flagSelect.value; // Current flag value
                    const ruleValue = ruleSelect.value; // Current rule value
                    console.log("Flag:", flagValue, "Rule:", ruleValue);

                    // Toggle the visibility of additional fields
                    if (flagValue === 'user ') {
                        additionalFields.style.display = 'block';
                    } else {
                        additionalFields.style.display = 'none';
                    }

                    // Reset attributes
                    departmentSelect.removeAttribute('required');
                    sectorSelect.removeAttribute('required');
                    departmentSelect.removeAttribute('disabled');

                    // Apply rules for dynamic validation
                    if (ruleValue == 3 && flagValue === 'user ') {
                        departmentSelect.setAttribute('required', true);
                    } else if (ruleValue == 4) {
                        sectorSelect.setAttribute('required', true);
                        departmentSelect.setAttribute('disabled', true);
                    }
                }

                // Attach event listeners
                flagSelect.addEventListener('change', handleDynamicFields);
                ruleSelect.addEventListener('change', handleDynamicFields);

                // Trigger on load
                handleDynamicFields();
            });




            $(document).ready(function() {
                $('#input13').change(function() {
                    if ($(this).val() === 'user ') {
                        $('#additionalFields').css('display', 'block');
                    } else {
                        $('#additionalFields').css('display', 'none');
                    }
                });
            });
            // $(document).ready(function() {
            $('.select2').select2({
                dir: "rtl"
            });
            //});
        </script>
        <script>
            // $(document).ready(function() {

            // $(document).ready(function() {
            $('#Provinces').on('change', function() {
                var Provinces_id = $(this).val();


                if (Provinces_id) {
                    $.ajax({
                        url: '/getRegion/' + Provinces_id,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            $('#region').empty();
                            $('#region').append('<option selected> اختار من القائمة </option>');
                            $.each(data, function(key, employee) {
                                console.log(employee);
                                $('#region').append('<option value="' + employee.id + '">' +
                                    employee
                                    .name + '</option>');
                                $('#region').trigger('change');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log('Error:', error);
                            console.log('XHR:', xhr.responseText);
                        }
                    });
                } else {
                    $('#region').empty();
                }
            });
            // });
        </script>
        <script>
            $(document).ready(function() {
                $('input[name="type_military"]').on('change', function() {
                    /*  if ($(this).val() === 'ضابط') {
                         alert('opt1');
                     } else if ($(this).val() === 'مهني') {
                         alert('opt2')
                     } */
                    getgrades(this.value)
                });
                $('#sector').on('change', function() {
                    /*  if ($(this).val() === 'ضابط') {
                         alert('opt1');
                     } else if ($(this).val() === 'مهني') {
                         alert('opt2')
                     } */
                    getDepartment(this.value)
                });
            });
            // JavaScript to handle radio button change events

            function getgrades(id) {

                // Create the URL with query parameters
                var url = '/get-grades?violation_type=' + id;

                $.ajax({
                    url: url,
                    type: 'GET', // Use GET method
                    success: function(response) {
                        console.log(id);

                        console.log(response); // Log the response

                        // Clear the current grade options
                        var gradeSelect = document.getElementById('gradeSelect');
                        gradeSelect.innerHTML = '<option selected disabled>اختار من القائمة</option>';

                        // Populate the grade select with new options
                        response.forEach(function(grade) { // Use `response` instead of `data`
                            var option = document.createElement('option');
                            option.value = grade.id;
                            option.textContent = grade.name;
                            gradeSelect.appendChild(option);
                        });
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('Error fetching grades:', textStatus, errorThrown);
                    }
                });
            }
            $(document).ready(function() {
                // Retrieve the old department ID from Blade or a pre-selected value for editing
                var oldDepartment = '{{ old('department_id', $user->department_id ?? '') }}';
                var sectorId = $('#sector').val(); // Get the pre-selected sector ID if exists

                // Populate departments if sector ID exists on page load
                if (sectorId) {
                    getDepartment(sectorId, oldDepartment);
                }

                // Fetch and populate departments when the sector changes
                $('#sector').on('change', function() {
                    var newSectorId = $(this).val();
                    getDepartment(newSectorId, null); // Reset oldDepartment to null on sector change
                });
            });

            function getDepartment(sectorId, oldDepartment) {
                if (!sectorId) return; // Ensure sectorId is provided

                var url = '/get-deprt-sector?sector=' + sectorId; // API endpoint for fetching departments

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        // Safely clear and repopulate the department dropdown
                        var $departmentDropdown = $('#department_id');
                        $departmentDropdown.empty(); // Clear existing options
                        $departmentDropdown.append('<option selected disabled>اختار من القائمة</option>');

                        // Populate dropdown with department options
                        $.each(response, function(key, department) {
                            $departmentDropdown.append(
                                `<option value="${department.uuid}">${department.name}</option>`
                            );
                        });

                        // Set the old department value after populating
                        if (oldDepartment) {
                            $departmentDropdown.val(oldDepartment);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('Error fetching departments:', textStatus, errorThrown);
                    }
                });
            }


            // function getDepartment(id) {

            //     console.log(id);

            //     var url = '/get-deprt-sector?sector=' + id;

            //     $.ajax({
            //         url: url,
            //         type: 'GET', // Use GET method
            //         success: function(response) {
            //             console.log(response);


            //             // Clear the current grade options
            //             var department_id = document.getElementById('department_id');
            //             department_id.innerHTML = '<option selected disabled>اختار من القائمة</option>';

            //             // Populate the grade select with new options
            //             response.forEach(function(grade) { // Use `response` instead of `data`
            //                 var option = document.createElement('option');
            //                 option.value = grade.id;
            //                 option.textContent = grade.name;
            //                 department_id.appendChild(option);
            //             });
            //         },
            //         error: function(jqXHR, textStatus, errorThrown) {
            //             console.error('Error fetching grades:', textStatus, errorThrown);
            //         }
            //     });

            // }
        </script>
        {{-- <script>
    document.addEventListener('DOMContentLoaded', function() {
        const radios = document.getElementsByName('solderORcivil');
        let selectedValue;

        // Function to show/hide the military section based on the selected value
        function toggleMilitarySection(value) {
            const militarySection = document.getElementById('type_military_id');
            if (value === "military") {
                militarySection.style.display = "block";
                $('#input12Div').fadeIn('fast');

            } else {
                militarySection.style.display = "none";
                $('#input12Div').fadeOut('fast');
            }
            $('#military_number_id').fadeOut('fast');
            $('#police').prop('checked', false);
            $('#police_').prop('checked', true);
        }

        // Check initial selection
        for (let i = 0; i < radios.length; i++) {
            if (radios[i].checked) {
                selectedValue = radios[i].value;
                break;
            }
        }

        // Call the function to toggle visibility based on initial selection
        toggleMilitarySection(selectedValue);

        // Handle change event
        radios.forEach((radio) => {
            radio.addEventListener('change', function() {
                if (radio.checked) {
                    toggleMilitarySection(radio.value);
                }
            });
        });
    });
</script> --}}

        {{-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            const radios = document.getElementsByName('type_military');
            let selectedValue;

            // Function to show/hide the military section based on the selected value
            function toggleMilitarySection(value) {
                const militarySection = document.getElementById('military_number_id');
                if (value === "police") {
                    militarySection.style.display = "block";
                } else {
                    militarySection.style.display = "none";
                }
            }

            // Check initial selection
            for (let i = 0; i < radios.length; i++) {
                if (radios[i].checked) {
                    selectedValue = radios[i].value;
                    break;
                }
            }

            // Call the function to toggle visibility based on initial selection
            toggleMilitarySection(selectedValue);

            // Handle change event
            radios.forEach((radio) => {
                radio.addEventListener('change', function() {
                    if (radio.checked) {
                        toggleMilitarySection(radio.value);
                    }
                });
            });
        });
    </script> --}}


        {{-- <script>
    document.addEventListener("DOMContentLoaded", function() {
        const checkbox = document.getElementById("myCheckbox");
        const grade = document.getElementById("grade");

        checkbox.addEventListener("change", function() {
            if (checkbox.checked) {
                grade.style.display = "block";
            } else {
                grade.style.display = "none";
            }

        });
    });


    // In your Javascript (external .js resource or <script> tag)

</script> --}}

        <script>
            function togglePasswordVisibility() {
                var passwordInput = document.getElementById('input3');
                var toggleIcon = document.getElementById('toggleIcon');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    toggleIcon.classList.remove('fa-eye');
                    toggleIcon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    toggleIcon.classList.remove('fa-eye-slash');
                    toggleIcon.classList.add('fa-eye');
                }
            }
        </script>
    @endsection
