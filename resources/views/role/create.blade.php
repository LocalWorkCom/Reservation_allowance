@extends('layout.main')
@section('content')
@section('title')
@endsection
<div class="row " dir="rtl">
    <div class="container  col-11" style="background-color:transparent;">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item "><a href="/">الرئيسيه</a></li>

                <li class="breadcrumb-item"><a href="{{ route('rule.index') }}">المهام</a></li>

                <li class="breadcrumb-item active" aria-current="page"> <a href=""> اضافه </a></li>
            </ol>
        </nav>
    </div>
</div>
<div class="row">
    <div class="container welcome col-11">
        <p>المـــــــهام</p>
    </div>
</div>
<div class="row">
    <div class="container  col-11 mt-3 p-0 ">
        <div class="container col-10 mt-5 mb-5 pb-5" style="border:0.5px solid #C7C7CC;">

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



            <form action="{{ route('rule.store') }}" method="post" class="text-right">
                @csrf

                <div class="form-row mx-md-2 mt-4 d-flex justify-content-center flex-row-reverse">

                    <div class="form-group col-md-12">
                        <label for="nameus"> الدور</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                </div>
                <div class="form-row mx-md-2 mt-4 d-flex justify-content-center flex-row-reverse">
                    <div class="form-group col-md-12">
                        <label for="department">الادارة</label>
                        <select class="custom-select custom-select-lg mb-3" name="department_id" id="department_id">
                            <option selected disabled>اختر من الادارات الاتيه</option>
                            @foreach ($alldepartment as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>



                    <div class="form-row mx-md-2 d-flex justify-content-center text-right">
                        <div class="form-group ">
                            <div class="row">
                                <div class="col-12">
                                    <label for="department">تحديد الصلاحيات </label>
                                </div>
                                <div class="col-12 my-2">
                                    <div class="form-check">
                                        <input type="checkbox" id="selectAll"
                                           class="form-check-input ">
                                        <label class="form-check-label mx-2 text-danger" for="selectAll">اختار الكل</label>
                                    </div>
                                </div>

                                <!-- Loop through each grouped permission -->
                                @foreach ($groupedPermissions as $group => $permissions)
                                <div class="col-12 my-3">
                                    <hr>
    <h4 class="text-info "> : {{ __('permissions.' . $group) }}</h4>
    <!-- Group Title -->

    <div class="d-flex flex-wrap justify-content-end gap-3">
        @foreach ($permissions as $item)
            <div class="form-check my-2">
                <input type="checkbox" id="exampleCheck{{ $item->id }}"
                    value="{{ $item->id }}" name="permissions_ids[]"
                    class="form-check-input selectPermission">
                <label class="form-check-label mx-2" for="exampleCheck{{ $item->id }}"
                    style="font-size:20px;">
                    {{ __('permissions.' . $item->name) }}
                </label>
            </div>
          
        @endforeach
    </div>
</div>

                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>
                <!-- </div> -->
                <!-- Save button -->
                <div class="container col-12">
                    <div class="form-row my-5">
                        <button type="submit" class="btn-blue">حفظ</button>
                    </div>
                </div>
                <br>
            </form>

        </div>
    </div>
</div>
{{-- <div class="col-lg-6">
                <div class="bg-white p-5">
                    {!! $dataTable->table(['class' => 'table table-bordered table-hover dataTable']) !!}
                </div>
            </div> --}}
</div>

</div>


</section>
<script>
document.getElementById('selectAll').addEventListener('click', function(event) {
    var selectAllChecked = event.target.checked;
    var checkboxes = document.querySelectorAll('.selectPermission');

    checkboxes.forEach(function(checkbox) {
        checkbox.checked = selectAllChecked;
    });
});
</script>


<script>
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
</script>


@endsection