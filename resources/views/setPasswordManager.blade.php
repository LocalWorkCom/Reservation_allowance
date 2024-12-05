<?php
@session_start();
@session_destroy();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>شئون القوة أنشاء كلمة السر </title>
    <!-- Cairo Font -->
    <script type="application/javascript" src="{{ asset('frontend/js/bootstrap.min.js')}}"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="{{ asset('frontend/styles/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/styles/all.min.css') }}">

    <link href="{{ asset('frontend/styles/font-awesome.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('frontend/styles/webfonts/fa-regular-400.woff2') }}">
    <link rel="stylesheet" href="{{ asset('frontend/styles/webfonts/fa-regular-400.woff2') }}">
    <!-- Bootstrap-->




    <link href="{{ asset('frontend/styles/bootstrap.min.css') }}" rel="stylesheet" id="bootstrap-css">
    <link rel="stylesheet" href="{{ asset('frontend/styles/login-styles.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/styles/login-responsive.css') }}" />
</head>

<body>

    <div class="container pt-5 pb-5">
        <div class="row col-12 pt-5">
            <div class=" col-md-4 col-sm-2">
                <img src="{{ asset('frontend/images/logo.svg') }}" alt="logo" class="logo">
            </div>
            <div class=" col-md-8 col-sm-12 col-12">
                <h5 class="login-h5">وزارة الداخلــــــــــــــــــية</h5>
                <p class="login-p">الادارة العامة لشئون القوة</p>
                <h2 class="login-h2">المطــــور</h2>
            </div>
        </div>
        <div class=" row col-12 d-flex justify-content-between">
            <div class="col-5 col-md-5 d-block reset-pass">
                <form action="{{ route('reset_password') }}" method="post">
                    @csrf
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
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
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <input type="hidden" name="number" value="{{ request()->segment(2) }}">
                    
                    <label for="temp_password" class="login-label-2">كلمة المرور المؤقتة</label> <br>
                    <input type="password" name="temp_password" id="temp_password" class="login-input"><br>                    

                    <label for="username" class="login-label-2">ادخل كلمه المرور</label> <br>
                    <input type="password" name="password" id="username" class="login-input"><br>

                    <label for="username" class="login-label-2">تاكيد كلمه المرور</label> <br>
                    <input type="password" name="password_confirm" id="username" class="login-input"><br>


                    <div class="btns  ">
                        <button class="btn1" type="submit"> ارسال </button> &nbsp; &nbsp; &nbsp;
                    </div>
                </form>
            </div>
            <div class="col-7 col-md-6">
                <img src="{{ asset('frontend/images/login.svg') }}" alt="background" class="background">
            </div>
        </div>
    </div>

</body>

</html>
