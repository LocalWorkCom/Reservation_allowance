@extends('layout.main')

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
                    <form action="{{ route('sub_departments.update', $department->id) }}" method="POST" enctype="multipart/form-data">
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
                    {{-- {{ dd($department) }} --}}
                        <div class="form-row mx-3 mt-4 d-flex justify-content-center">
                            <div class="form-group col-md-10 mx-md-2">
                                <label for="sector">اختر القطاع </label>
                                <input type="hidden" name="parent" value="{{$department->sector_id ? $department->sector_id : $sect->sector_id }}">
                                <input type="text" class="form-control" id="sector" value="{{$department->sector_id ? $department->sectors->name : $sect->sectors->name }}" @if ($department->sector_id != null)
                                disabled
                                @endif >
                                <input type="hidden" class="form-control" name="sector_id"  value="{{ $department->sector_id }}" disabled>

                                @error('sector')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-10 mx-md-2">
                                <label for="name">أسم الأداره الرئيسية</label>
                                <input type="text" name="name" class="form-control" value="{{ $department->name}}">
                                @error('name')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-10 mx-md-2">
                                <label for="budget">ميزانية بدل حجز</label>
                                <input type="text" name="budget" class="form-control" value="{{$department->reservation_allowance_amount }}">
                                @error('budget')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-10 mx-md-2">
                                <label for="">صلاحيه الحجز</label>
                                <input type="checkbox" class="toggle-radio-buttons mx-2" value="1" id="part" @if($department->reservation_allowance_type == 1 ||$department->reservation_allowance_type == 3 )checked @endif
                                    name="part[]">
                                <label for="part"> حجز كلى</label><input type="checkbox"
                                    class="toggle-radio-buttons mx-2" value="2" id="part" @if($department->reservation_allowance_type == 2 ||$department->reservation_allowance_type == 3 )checked @endif
                                    name="part[]">
                                <label for="part">حجز جزئى</label>
                                @error('budget')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-10 mx-md-2">
                                <label for="mangered">المدير</label>
                                <select name="manger" id="mangered" class="form-control " required>
                                    <option value="">اختار المدير</option>
                                    @foreach ($managers as $user)
                                        <option value="{{ $user->id }}"@if ($department->manger == $user->id)
                                            selected
                                        @endif>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                @error('manger')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror

                            </div>

                        </div>
                        <div class="form-row mx-2 d-flex justify-content-center">

                            <div class="form-group col-md-10 mx-md-2">
                                <label for="description">الوصف </label>
                                <input type="text" name="description" class="form-control"
                                    value="{{ $department->description}}">
                                @error('description')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="form-group col-md-10 mx-md-2">
                                <label for="employess">الموظفين </label>
                                <select name="employess[]" id="employess" class="form-group col-md-12 " multiple
                                    dir="rtl"
                                    style=" height: 150px;font-size: 18px;border: 0.2px solid lightgray; overflow-y: auto;">
                                    @foreach ($employees as $item)
                                        <option value="{{ $item->id }}" @if($item->department_id == $department->id ) selected @endif>{{ $item->name }}</option>
                                    @endforeach
                                </select>

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
@endsection
