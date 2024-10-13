@extends('layout.main')
@section('content')
@section('title')
اضافة
@endsection
<div class="row " dir="rtl">
<div class="container  col-11" style="background-color:transparent;">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item "><a href="/">الرئيسيه</a></li>

            @if (url()->current() == url('/users_create/0'))
            <li class="breadcrumb-item"><a href="{{ route('user.index', 0) }}">المستخدمين</a></li>
            @elseif (url()->current() == url('/users_create/1'))
            <li class="breadcrumb-item"><a href="{{ route('user.employees', 1) }}">الموظفين</a></li>
            @endif
            <li class="breadcrumb-item active" aria-current="page"> <a href=""> اضافة </a></li>
        </ol>
    </nav>
</div>
</div>
<div class="row ">
    <div class="container welcome col-11">
        @if (url()->current() == url('/users_create/0'))
        <p>المستخدمين</p>
        @elseif (url()->current() == url('/users_create/1'))
        <p>الموظفين</p>
        @endif
        <!-- <p> المستخدمين </p> -->
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
            <div class="">


                {{-- {{dd($flag)}} --}}

                <form action="{{ route('reservation_allowances.store') }}" method="post" class="text-right" enctype="multipart/form-data">
                    @csrf

                    <div class="form-row mx-md-2 mt-4 d-flex justify-content-center">

                    <input name="department_type" id="department_type" type="hidden"
                                    value="{{Auth::user()->department_id == null ? 1 : 2}}">

                                    <div class="d-flex">
                                        {{-- @if (Auth::user()->hasPermission('create reservation_allowances')) --}}
                                        <!-- <label for="Civil_number" class="d-flex "> <i class="fa-solid fa-asterisk" style="color:red; font-size:10px;"></i>اختار </label> -->
                                        <select class="custom-select custom-select-lg select2" name="sector_id"
                                            id="sector_id" required>
                                            <option value="0" selected>اختار القطاع</option>
                                            @foreach ($sectors as $sector)
                                            <option value="{{ $sector->id }}">
                                                {{ $sector->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="d-flex mx-2">
                                        {{-- @if (Auth::user()->hasPermission('create reservation_allowances')) --}}
                                        <!-- <label for="Civil_number" class="w-75"> <i class="fa-solid fa-asterisk" style="color:red; font-size:10px;"></i>اختار الادارة</label> -->
                                        <select class="custom-select custom-select-lg select2" name="departement_id"
                                            id="departement_id">
                                            <option value="0" selected>اختار الادارة</option>
                                        </select>
                                    </div>

                        <div class="form-group col-md-10 mx-2">
                                <label for="Civil_number"> <i class="fa-solid fa-asterisk" style="color:red; font-size:10px;"></i>
                                    رقم الهوية</label>
                                <select class="custom-select custom-select-lg mb-3 select2" name="Civil_number" id="Civil_number">
                                    <option selected disabled>اختار من القائمة</option>
                                    @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ old('Civil_number') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->file_number }}</option>
                                    @endforeach
                                </select>
                        </div>

                        <div class="form-group col-md-10 mx-2">
                            <label for="type">صلاحية الحجز</label>
                            <div class="d-flex justify-content-end">
                                @if(auth()->user()->department->reservation_allowance_type == 1 || auth()->user()->department->reservation_allowance_type == 3)
                                <div class="d-flex justify-content-end">
                                    <label for="">  حجز كلى</label>
                                    <input type="radio" id="type" name="type" class="form-control" checked value="1" required>
                                </div>
                                @endif
                                @if(auth()->user()->department->reservation_allowance_type == 2 || auth()->user()->department->reservation_allowance_type == 3)
                                <div class="d-flex justify-content-end mx-4">
                                    <label for="">  حجز جزئى</label>
                                    <input type="radio" id="type" name="type" class="form-control" value="2" required>
                                </div>
                                @endif
                            </div>
                            <span class="text-danger span-error" id="type-error" dir="rtl"></span>
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

</div>

</div>
<script>
// $(document).ready(function() {
$('.select2').select2({
    dir: "rtl"
});
//});
</script>

<script>
 $(document).ready(function() {


    $(document).on("change", "#sector_id", function() {
        var sectorid = this.value;
        var department_type = document.getElementById('department_type').value;
        var map_url = "{{route('reservation_allowances.get_departement',['id','type'])}}";
        map_url = map_url.replace('id', sectorid);
        map_url = map_url.replace('type', department_type);
        $.get(map_url, function(data) {
            $("#departement_id").html(data);
        });
    });


});
</script>

@endsection
