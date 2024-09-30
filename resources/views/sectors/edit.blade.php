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
                    <li class="breadcrumb-item active" aria-current="page"> <a href=""> تعديل القطاع</a></li>
                </ol>
            </nav>
        </div>
    </div>
    {{-- <div class="row ">
        <div class="container welcome col-11">
            <p> القطــــاعات </p>
        </div>
    </div> --}}
    <br>
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
    <form class="edit-grade-form" action=" {{ route('sectors.update') }}" method="POST">
        @csrf
        <div class="row" dir="rtl">
            <div id="first-container" class="container moftsh col-11 mt-1 p-0 pb-3">

                <div class="form-row mx-2 mb-2">
                    <h3 class="pt-3 px-md-4 px-3">اضف قطاع</h3>
                    <div class="input-group moftsh px-md-4 px-3 pt-3">
                        <label class="pb-3" for="nameedit">ادخل اسم القطاع</label>
                        <input type="text" id="nameedit" name="nameedit" class="form-control"
                            value="{{ $data->name }}" placeholder="قطاع واحد" dir="rtl" required />
                        <span class="text-danger span-error" id="nameedit-error"></span>

                    </div>
                </div>
                         <input type="hidden" name="id" value="{{ $data->id }}">

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

        {{-- <div class="row" dir="rtl">
            <div id="second-container" class="container moftsh col-11 mt-3 p-0 pb-3 hidden">
                <h3 class="pt-3 px-md-4 px-3">اضف محافظات داخل قطاع</h3>
                <div class="form-row mx-2">
                    <div class="form-group moftsh px-md-4 px-3 pt-3">
                        <h4 style="color: #274373; font-size: 24px;">حدد المحافظات المراد اضافتها</h4>
                    </div>
                </div>
                <div class="form-row col-11 mb-2 mt-3 mx-md-2">
                    @foreach ($governments as $government)
                        <div class="form-group col-3 d-flex mx-md-3">
                            <input type="checkbox" name="governmentIDS[]" value="{{ $government->id }}"
                                @if (in_array($government->id, $data->governments_IDs)) checked @endif id="governmentIDS_{{ $government->id }}">
                            <label for="governmentIDS_{{ $government->id }}">{{ $government->name }}</label>
                        </div>
                    @endforeach --}}
        {{-- @foreach ($governments as $government)
                    <option value="{{ $government->id }}"
                        @if (in_array($government->id, $sector->governments_IDs)) selected @endif>
                        {{ $government->name }}
                    </option>
                @endforeach --}}

        {{-- @foreach (getgovernments() as $government)
                    <div class="form-group col-3 d-flex mx-md-3">
                        <input type="checkbox" name="governmentIDS[]" value="{{ $government->id }}"
                            @if (isset($checkedGovernments[$government->id])) checked @endif
                            id="governmentIDS_{{ $government->id }}">
                        <label for="governmentIDS_{{ $government->id }}">{{ $government->name }}</label>
                    </div>
                @endforeach --}}
        {{-- <input type="hidden" name="id" value="{{ $data->id }}">
                </div>
                <span class="text-danger span-error" id="governmentIDS-error"></span>
                <div class="container col-12">
                    <div class="form-row d-flex justify-content-end mt-4 mb-3">
                        <button type="submit" class="btn-blue">
                            <img src="{{ asset('frontend/images/white-add.svg') }}" alt="img" height="20px"
                                width="20px"> اضافة
                        </button>
                        <button type="button" id="back-button" class="btn-back mx-2">
                            <img src="{{ asset('frontend/images/previous.svg') }}" alt="img" height="20px"
                                width="20px"> السابق</button>

                    </div>
                </div>
            </div>
        </div> --}}
    </form>
@endsection
