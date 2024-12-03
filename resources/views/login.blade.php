<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>شئون القوة - تسجيل الدخول </title>
    <link rel="stylesheet" href="{{ asset('frontend/styles/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/styles/login-styles.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/styles/login-responsive.css') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('frontend/images/favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32"
        href="{{ asset('frontend/images/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16"
        href="{{ asset('frontend/images/favicon/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('frontend/images/favicon/site.webmanifest') }}">
</head>

<body>
    <div class="container pt-5 pb-5">
        <div class="row col-12 pt-5">
            <div class="col-md-4 col-sm-2">
                <img src="{{ asset('frontend/images/logo.svg') }}" alt="logo" class="logo">
            </div>
            <div class="col-md-8 col-sm-12 col-12">
                <h5 class="login-h5">وزارة الداخلــــــــــــــــــية</h5>
                <p class="login-p">الادارة العامة لشئون القوة</p>
                <h2 class="login-h2">بدل حجز</h2>
            </div>
        </div>
        <div class="row col-12 d-flex justify-content-between">
            <div class="col-5 col-md-5 d-block">
                {{-- <form action="{{ route('login') }}" method="post"> --}}
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
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
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <label for="username" class="login-label">اسم المستخدم</label> <br>
                <input type="text" name="number" id="username" class="login-input"> <br>

                <div style="display: none;" id="password_div">

                    <label for="password" class="login-label">كلمة المرور</label> <br>
                    <div class="password-container">
                        <input type="password" name="password" id="password" class="login-input" value="">
                        <label class="toggle-password" onclick="togglePasswordVisibility()">
                            <i id="toggleIcon" class="fa fa-eye"></i>
                        </label>
                    </div>
                </div>

                <div class="btns">
                    <button class="btn1" type="submit" id="button_submit"> التالي</button>
                </div>
                {{-- </form> --}}
            </div>
            <div class="col-7 col-md-6">
                <img src="{{ asset('frontend/images/login.svg') }}" alt="background" class="background">
            </div>
        </div>
    </div>
    <script src="{{ asset('frontend/js/jquary.js') }}"></script>

    <!-- Include JS files -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="{{ asset('frontend/js/bootstrap.min.js') }}"></script>
    <script>
        function togglePasswordVisibility() {
            var passwordField = document.getElementById('password');
            var toggleIcon = document.getElementById('toggleIcon');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        $(document).ready(function() {
            $('#button_submit').click(function(e) {
                e.preventDefault(); // Prevents the form from submitting normally

                // Get the username and password values from the input fields
                var username = $('#username').val();
                var password = $('#password').val();

                var isPasswordVisible = $('#password_div').is(
                    ':visible'); // Check if password div is visible


                // Make the AJAX call only if the username is provided
                if (username && isPasswordVisible) {
                    // If password is empty, validate before sending the login request
                    // If password is provided, send login request
                    $.ajax({
                        url: "{{ route('login.submit') }}", // The route or endpoint to which the request will be sent

                        data: {
                            number: username, // Username value from the input field
                            password: password // Password value from the input field
                        },
                        success: function(response) {

                            if (response.success) {
                                window.location.href =
                                    '/home'; // Redirect on success (example)
                            } else {


                                console.log(response);

                                Swal.fire({
                                    title: 'خطأ',
                                    text: 'كلمة السر او المستخدم خطأ',
                                    icon: 'error',
                                    confirmButtonText: 'إلغاء',
                                    confirmButtonColor: '#3085d6',

                                });
                            }

                        },
                        error: function(xhr, status, error) {
                            // Handle any errors here
                            console.error('AJAX Error:', status, error);
                        }
                    });

                } else {

                    // If password is not provided, check if the username exists
                    $.ajax({
                        url: "{{ route('check.login') }}", // The route or endpoint to check username
                        type: 'GET', // HTTP method
                        data: {
                            number: username // Username value from the input field
                        },
                        success: function(response) {

                            // Check if the response is true (username exists)
                            if (response == 1) {
                                window.location.href = '/set-password/' + username;

                          
                            } else {
                                if (response == -1) {
                                    Swal.fire({
                                        title: 'خطأ',
                                        text: 'لا يسمح لك بدخول الهيئة',
                                        icon: 'error',
                                        confirmButtonText: 'إلغاء',
                                        confirmButtonColor: '#3085d6',

                                    });
                                    $('#password_div')
                                    .hide(); // Hide password field if username is invalid
                                } else {
                                    console.log(response);

                                    $('#password_div')
                                        .show(); // Show password field if username is valid
                                  
                                }
                              



                            }
                        },
                        error: function(xhr, status, error) {
                            // Handle any errors here
                            console.error('AJAX Error:', status, error);
                        }
                    });
                }



            });
        });
    </script>
</body>

</html>
