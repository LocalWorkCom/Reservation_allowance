@extends('layout.main')
@section('title')
    تعديل
@endsection
@section('content')


    <section>
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
                            <li class="breadcrumb-item"><a href="{{ route('user.employees', 'employee') }}">موظفين القوة</a></li>
                        @endif
                        <li class="breadcrumb-item active" aria-current="page"> <a href=""> تعديل </a></li>
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


        </div>

        <div class="row">
            <div class="container  col-11 mt-3 p-0 ">
                <div class="container col-10 mt-1 mb-5 pb-5  mt-5" style="border:0.5px solid #C7C7CC;">

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
                    {{-- {{ dd($user) }} --}}

                    <form action="{{ route('user.update', $user) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-row pt-5 pb-3 d-flex justify-content-around flex-row-reverse"
                            style="background-color:#f5f8fd; border-bottom:0.1px solid lightgray;">
                            <div class="form-group d-flex  justify-content-center col-md-5 mx-2 pb-2">
                                {{-- {{ dd($user->type) }} --}}
                                <div class="radio-btns mx-md-4 ">
                                    <input type="radio" class="form-check-input" id="male" name="gender"
                                        value="man" style="height:20px; width:20px;"
                                        @if ($user->type == 'man') checked @endif>
                                    <label class="form-check-label mx-2" for="male">ذكر</label>
                                </div>
                                <div class="radio-btns mx-md-4 ">
                                    <input type="radio" class="form-check-input" id="female" name="gender"
                                        value="female" style="height:20px; width:20px;"
                                        @if ($user->type == 'female') checked @endif>
                                    <label class="form-check-label mx-md-2" for="female">انثى</label>
                                </div>

                                <label for="input44">الفئة</label>
                            </div>
                            <div class="form-group d-flex justify-content-center col-md-5 mx-2 pb-2">
                                @foreach ($violationTypeName as $key => $violation)
                                    <div class="radio-btns" style="margin-left: 1.5rem; margin-right: 1.5rem;">
                                        <input type="radio" class="form-check-input" id="police_{{ $key }}"
                                            name="type_military" value="{{ $violation->id }}"
                                            style="height:20px; width:20px;"
                                            @if ($user->grade && $violation->id == $user->grade->type) checked @endif>
                                        <label class="form-check-label mx-2" for="police_{{ $key }}"
                                            style="margin-left: 0.5rem;">{{ $violation->name }}</label>
                                    </div>
                                @endforeach
                                <label for="type_military" style="margin-left: 1.5rem; margin-right: 1.5rem;">نوع
                                    العسكرى</label>
                            </div>
                            {{-- <div class="form-group d-flex  justify-content-center col-md-5 mx-2 pb-2">


                                <div class="radio-btns mx-md-4 ">
                                    <input type="radio" class="form-check-input" id="solder" name="solderORcivil"
                                        value="military" style="height:20px; width:20px;"
                                        @if ($user->employee_type != 'civil') checked @endif>
                                    <label class="form-check-label mx-2" for="solder">عسكرى</label>
                                </div>
                                <div class="radio-btns mx-md-4 ">
                                    <input type="radio" class="form-check-input" id="civil" name="solderORcivil"
                                        value="civil" style="height:20px; width:20px;"
                                        @if ($user->employee_type == 'civil') checked @endif>
                                    <label class="form-check-label mx-2" for="civil">مدنى</label>
                                </div>

                                <label for="input44"> التصنيف</label>
                            </div> --}}
                        </div>


                        <div class="form-row mx-3 d-flex justify-content-center flex-row-reverse">
                            <div class="form-group col-md-5 mx-2">
                                <label for="input1"><i class="fa-solid fa-asterisk"
                                        style="color:red; font-size:10px;"></i> الاسم</label>
                                <input type="text" id="input1" name="name" class="form-control" placeholder="الاسم"
                                    value="{{ $user->name }}" dir="rtl">
                            </div>
                            <div class="form-group col-md-5 mx-2">
                                <label for="input2">
                                    {{-- @if ($user->flag == 'user')
                                        <i class="fa-solid fa-asterisk" style="color:red; font-size:10px;"></i>
                                    @endif --}}
                                    البريد الالكتروني
                                </label>
                                <input type="text" id="input2" name="email" class="form-control"
                                    placeholder=" البريد الالكترونى" value="{{ $user->email }}" dir="rtl">
                            </div>
                        </div>

                        <div class="form-row mx-3 d-flex justify-content-center flex-row-reverse">
                            <div class="form-group col-md-5 mx-2">
                                <label for="input4"><i class="fa-solid fa-asterisk"
                                        style="color:red; font-size:10px;"></i> الهاتف</label>
                                <input type="text" id="input4" name="phone" class="form-control"
                                    placeholder=" رقم الهاتف" value="{{ $user->phone }}" dir="rtl">
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
                                    value="{{ $user->address_1 }}"></textarea>
                            </div>

                            <div class="form-group col-md-5 mx-2">
                                <label for="input11"> <i class="fa-solid fa-asterisk"
                                        style="color:red; font-size:10px;"></i> رقم المدنى</label>
                                <input type="text" id="input11" name="Civil_number" class="form-control"
                                    placeholder="رقم المدنى" value="{{ $user->Civil_number }}" dir="rtl">
                            </div>

                        </div>

                        <div class="form-row  mx-3 d-flex justify-content-center flex-row-reverse">
                            <div class="form-group col-md-5 mx-2">
                                <label for="input9"> المسمي الوظيفي</label>
                                <input type="text" id="input9" name="job_title" class="form-control"
                                    placeholder="المسمي الوظيفي" value="{{ $user->job_title }}" dir="rtl">
                            </div>
                            <div class="form-group col-md-5 mx-2">
                                <label for="country_select">الجنسية</label>
                                <select id="country_select" name="nationality" class="form-control">
                                    <option selected disabled>اختار من القائمة</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}"
                                            {{ $user->nationality == $country->id ? 'selected' : '' }}>
                                            {{ $country->country_name_ar }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <div class="form-row  mx-3 d-flex justify-content-center flex-row-reverse">

                            <div class="form-group col-md-5 mx-2"id="military_number_id">
                                <label for="input6"> رقم العسكرى</label>
                                <input type="text" id="input6" name="military_number" class="form-control"
                                    placeholder="رقم العسكرى" value="{{ $user->military_number }}" dir="rtl">
                            </div>
                            <div class="form-group col-md-5 mx-2"id="input12Div">
                                <label for="input12"> <i class="fa-solid fa-asterisk"
                                        style="color:red; font-size:10px;"></i> رقم الملف</label>
                                <input type="text" id="input12" name="file_number" class="form-control"
                                    placeholder="رقم الملف" value="{{ $user->file_number }}" dir="rtl">
                            </div>
                        </div>
                        <div class="form-row mx-2 d-flex justify-content-center flex-row-reverse">
                            <div class="form-group col-md-10">
                                <label for="input13">هل يمكن لهذا الموظف أن يكون مستخدم؟</label>
                                <select id="input13" name="flag" class="form-control">
                                    <!-- Dynamically set the selected option based on the user's flag value -->
                                    <option value="user" {{ $user->flag == 'user' ? 'selected' : '' }}>نعم</option>
                                    <option value="employee" {{ $user->flag == 'employee' ? 'selected' : '' }}>لا
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row mx-3 d-flex justify-content-center flex-row-reverse" id="additionalFields"
                            style="{{ $user->flag == 'user' ? 'visibility: visible;' : 'visibility: hidden;' }}">
                            <!-- Password field -->
                            <div class="form-group col-md-5 mx-2">
                                <label for="input3">
                                    <i class="fa-solid fa-asterisk" style="color:red; font-size:10px;"></i> الباسورد
                                </label>
                                <div class="password-container">
                                    <input type="password" id="input3" name="password" class="form-control"
                                        placeholder="الباسورد" dir="rtl">
                                    <label class="toggle-password" onclick="togglePasswordVisibility()">
                                        <i id="toggleIcon" class="fa fa-eye eye-icon"></i>
                                    </label>
                                </div>
                            </div>

                            <!-- Tasks (roles) field -->
                            <div class="form-group col-md-5 mx-2">
                                <label for="input7">
                                    <i class="fa-solid fa-asterisk" style="color:red; font-size:10px;"></i> المهام
                                </label>
                                <select id="input7" name="rule_id" class="form-control select2" placeholder="المهام">
                                    <option disabled>اختار من القائمة</option>
                                    @foreach ($rule as $item)
                                        @if ($item->name != 'localworkadmin')
                                            <option value="{{ $item->id }}"
                                                {{ $user->rule_id == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>



                        <div class="form-row mx-md-2  d-flex justify-content-center flex-row-reverse">
                            <div class="form-group col-md-5 mx-2">
                                <label for="sector">
                                    القطاع </label>
                                <select id="sector" name="sector" class="form-control " placeholder="القطاع">
                                    <option value="{{ null }}" selected>
                                        لا يوجد قسم محدد</option>
                                    @foreach ($sectors as $sector)
                                        <option value="{{ $sector->id }}"
                                            {{ old('sector', $user->sector) == $sector->id ? 'selected' : '' }}>
                                            {{ $sector->name }}</option>
                                    @endforeach

                                </select>
                            </div>

                            <div class="form-group col-md-5 mx-2">
                                <label for="department_id">
                                   
                                    الادارة
                                </label>
                                <select id="department_id" name="public_administration" class="form-control select2"
                                    placeholder="الادارة ">
                                    @if ($user->department_id == null)
                                        <option selected disabled>اختار من القائمة</option>
                                    @endif
                                    @foreach ($department as $item)
                                        <option value="{{ $item->id }}"
                                            {{ $user->department_id == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>

                        </div>
                        {{-- <div class="form-row mx-md-2  d-flex justify-content-center flex-row-reverse">

                            <div class="form-group col-md-5 mx-2">
                                <label for="input22">مدة الخدمة</label>
                                <input type="text" id="input22" name="end_of_service" class="form-control"
                                    placeholder="مدة الخدمة " value="{{ $user->length_of_service }}">
                            </div>
                        </div> --}}
                        <div class="form-row mx-2 mx-3 d-flex justify-content-center flex-row-reverse">

                            <div class="form-group col-md-5 mx-2">
                                <label for="gradeSelect">
                                    <i class="fa-solid fa-asterisk" style="color:red; font-size:10px;"></i>
                                    الرتبة
                                </label>
                                <select id="gradeSelect" name="grade_id" class="form-control ">
                                    <option value="">اختار من القائمة</option>
                                    @foreach ($grades as $item)
                                        <option value="{{ $item->id }}"
                                            {{ $user->grade_id == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>

                            </div>
                        </div>

                        <div class="form-row mx-2 mx-3 d-flex justify-content-center flex-row-reverse">

                            <div class="form-group col-md-5 mx-2">
                                <label for="input19">تاريخ الميلاد</label>
                                <input type="date" id="input19" name="date_of_birth" class="form-control"
                                    placeholder="تاريخ الميلاد" value="{{ $user->date_of_birth }}">
                            </div>
                            <div class="form-group col-md-5 mx-2">
                                <label for="input20">تاريخ الالتحاق</label>
                                <input type="date" id="input20" name="joining_date" class="form-control"
                                    placeholder="تاريخ الالتحاق" value="{{ $user->joining_date }}">
                            </div>
                        </div>

                        {{-- 
                        <div class="form-row mx-2 d-flex justify-content-center flex-row-reverse">
                            <div class="form-group col-md-10 mx-2">
                                <label for="input24"> الرتبة</label>
                                <select id="input24" name="grade_id" class="form-control select2"
                                    placeholder="الرتبة">
                                    @if ($user->grade_id == null)
                                        <option selected disabled>اختار من القائمة</option>
                                    @endif
                                    @foreach ($grade as $item)
                                        <option value="{{ $item->id }}"
                                            {{ $user->grade_id == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div> --}}
                        <div class="form-row mx-2 mx-2 d-flex justify-content-center flex-row-reverse">
                            <div class="form-group col-md-10">
                                <label for="input5"> الملاحظات</label>
                                <textarea type="text" id="input5" name="description" class="form-control" placeholder="الملاحظات"
                                    rows="3">{{ $user->description }}</textarea>
                            </div>
                        </div>
                        <div class="form-row mx-2 mx-2 d-flex justify-content-center flex-row-reverse">
                            <div class="form-group col-md-10">
                                <label for="input23">الصورة</label>
                                <input type="file" class="form-control" name="image" id="input23"
                                    placeholder="الصورة" value="{{ $user->image }}">
                            </div>

                            <div style="background-image:  url('{{ $user->image }}')">
                            </div>
                        </div>

                </div>

                <!-- Save button -->
                <div class="container col-10 mt-5 mb-5 ">
                    <div class="form-row col-10 " dir="ltr">
                        <button class="btn-blue " type="submit">
                            تعديل </button>
                    </div>
                </div>
                <br>
                </form>
            </div>
        </div>
        </div>
        </div>
        </div>

    </section>
    <script>
        $('.select2').select2({
            dir: "rtl"
        });
        $(document).ready(function() {
            $('#input13').change(function() {


                if ($(this).val() == 'user') {
                    console.log($(this).val());
                    $('#additionalFields').css('visibility', 'visible');

                } else {
                    $('#additionalFields').css('visibility', 'hidden');

                }
            });

            // Initialize the select2 plugin

        });
        $('#sector').on('change', function() {
            getDepartment(this.value)
        });


        // Function to toggle password visibility
        function togglePasswordVisibility() {
            var passwordInput = document.getElementById("input3");
            var toggleIcon = document.getElementById("toggleIcon");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggleIcon.classList.remove("fa-eye");
                toggleIcon.classList.add("fa-eye-slash");
            } else {
                passwordInput.type = "password";
                toggleIcon.classList.remove("fa-eye-slash");
                toggleIcon.classList.add("fa-eye");
            }
        }
    </script>
    <script>
        //     $(document).ready(function() {
        //     $('#sector').on('change', function() {
        //         var sector_id = $(this).val();


        //         if (sector_id) {
        //             $.ajax({
        //                 url: '/getGoverment/' + sector_id,
        //                 type: 'GET',
        //                 dataType: 'json',
        //                 success: function(data) {
        //                     $('#Provinces').empty();
        //                     $('#Provinces').append('<option selected> اختار من القائمة </option>');
        //                     $.each(data, function(key, employee) {               
        //                         console.log(employee);   
        //                         $('#Provinces').append('<option value="' + employee.id + '">' + employee.name + '</option>');
        //                     });                 
        //                 },
        //                 error: function(xhr, status, error) {
        //                     console.log('Error:', error);
        //                     console.log('XHR:', xhr.responseText);
        //                 }
        //             });
        //         } else {
        //             $('#Provinces').empty();
        //         }
        //     });
        // });

        $(document).ready(function() {
            function loadRegions() {
                var sector_id = $('#sector').val();
                // 
                var selectedProvincesId = '{{ $user->Provinces }}';
                if (sector_id) {
                    $.ajax({
                        url: '/getGoverment/' + sector_id,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            $('#Provinces').empty();
                            $('#region').empty();
                            $('#Provinces').append(
                                '<option selected value="null"> اختار من القائمة </option>');
                            $.each(data, function(key, employee) {
                                console.log(employee);
                                var selected = (employee.id == selectedProvincesId) ?
                                    'selected' : '';
                                $('#Provinces').append('<option value="' + employee.id + '" ' +
                                    selected + '>' + employee.name + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log('Error:', error);
                            console.log('XHR:', xhr.responseText);
                        }
                    });
                } else {
                    $('#Provinces').empty();
                    $('#region').empty();
                }
            }

            // Trigger loadRegions when Provinces changes
            $('#sector').on('change', loadRegions);

            // Trigger loadRegions when the page loads
            loadRegions();
        });

        $(document).ready(function() {
            function loadRegions() {
                var Provinces_id = $('#Provinces').val();
                var selectedregionId = '{{ $user->region }}';
                if (Provinces_id) {
                    $.ajax({
                        url: '/getRegion/' + Provinces_id,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            $('#region').empty();
                            $('#region').append(
                                '<option selected value="null"> اختار من القائمة </option>');
                            $.each(data, function(key, employee) {
                                console.log(employee);
                                var selected = (employee.id == selectedregionId) ? 'selected' :
                                    '';
                                $('#region').append('<option value="' + employee.id + '"  ' +
                                    selected + '>' + employee.name + '</option>');
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
            }

            // Trigger loadRegions when Provinces changes
            $('#Provinces').on('change', loadRegions);

            // Trigger loadRegions when the page loads
            loadRegions();
        });
    </script>
    <script>
        // $(document).ready(function() {
        // $('.select2').select2({  dir: "rtl"});
        //});
    </script>
    <script>
        $(document).ready(function() {
            $('.image-popup').click(function(event) {
                event.preventDefault();
                var imageUrl = $(this).data('image');
                var imageTitle = $(this).data('title');

                // Set modal image and title
                $('#modalImage').attr('src', imageUrl);
                $('#imageModalLabel').text(imageTitle);

                // Show the modal
                $('#imageModal').modal('show');
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // Get the current military type value from the database
            // var currentMilitaryType = $('input[name="type_military"]:checked')
            //     .val(); // Get the checked radio button value
            // if (currentMilitaryType) {
            //     getGrades(currentMilitaryType); // Populate grades based on the current value
            // }

            // Listen for changes in the radio button
            $('input[name="type_military"]').on('change', function() {

                getgrades(this.value)
            });

        });

        function getgrades(id) {
            // Create the URL with query parameters
            var url = '/get-grades?violation_type=' + id;

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    console.log(response); // Log the response for debugging

                    // Clear the current grade options
                    var gradeSelect = $('#gradeSelect');
                    gradeSelect.empty(); // Clear the current options

                    // Add default option
                    gradeSelect.append('<option>اختار من القائمة</option>');

                    // Populate the grade select with new options
                    response.forEach(function(grade) {
                        gradeSelect.append($('<option></option>').val(grade.id).text(grade.name));
                    });

                    // Re-initialize select2 to apply it on the new options
                    // gradeSelect.select2({
                    //     dir: "rtl" // RTL direction
                    // });
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Error fetching grades:', textStatus, errorThrown);
                }
            });
        }

        function getDepartment(id) {

            console.log(id);

            var url = '/get-deprt-sector?sector=' + id;

            $.ajax({
                url: url,
                type: 'GET', // Use GET method
                success: function(response) {
                    console.log(response);


                    // Clear the current grade options
                    var department_id = document.getElementById('department_id');
                    department_id.innerHTML = '<option selected disabled>اختار من القائمة</option>';

                    // Populate the grade select with new options
                    response.forEach(function(grade) { // Use `response` instead of `data`
                        var option = document.createElement('option');
                        option.value = grade.id;
                        option.textContent = grade.name;
                        department_id.appendChild(option);
                    });
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Error fetching grades:', textStatus, errorThrown);
                }
            });

        }
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
                /*  $('#police').prop('checked', false);
                 $('#police_').prop('checked', true); */
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

@endsection
