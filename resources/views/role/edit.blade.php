@extends('layout.main')
@section('content')
@section('title')
    تعديل
@endsection
{{-- <body> --}}
<section>

    <div class="row " dir="rtl">
        <div class="container  col-11" style="background-color:transparent;">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item "><a href="/">الرئيسيه</a></li>

                    <li class="breadcrumb-item"><a href="{{ route('rule.index') }}">المهام</a></li>

                    <li class="breadcrumb-item active" aria-current="page"> <a href=""> تعديل </a></li>
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
                {{-- {{ dd($user) }} --}}

                <form action="{{ route('rule_update', $rule_permission->id) }}" method="POST">
                    @csrf

                    <div class="form-row mx-md-2 mt-4 d-flex justify-content-center flex-row-reverse">

                        <div class="form-group col-md-10">
                            <label for="input8">الدور</label>
                            <input type="text" id="input8" name="name" class="form-control"
                                placeholder="الوظيفة" value="{{ $rule_permission->name }}" dir="rtl">
                        </div>
                    </div>
                    <div class="form-row mx-md-2 mt-4 d-flex justify-content-center flex-row-reverse">
                        <div class="form-group col-md-10">
                            <label for="input25"> القسم</label>
                            <select id="input25" name="department_id" class="form-control" placeholder="القسم">
                                @foreach ($alldepartment as $item)
                                    <option value="{{ $item->id }}"
                                        {{ $rule_permission->department_id == $item->id ? 'selected' : '' }}>
                                        {{ $item->name }}</option>
                                @endforeach

                            </select>
                        </div>
                    </div>


                    <div class="form-row mx-md-2 d-flex justify-content-center text-right">
                        <div class="form-group col-md-10">
                            <div class="row">
                                <label for="department" class="col-12">الصلاحية</label>

                                <div class="col-12 my-2">
                                    <div class="form-check">
                                        <input type="checkbox" id="selectAll"
                                            style="width: 20px; height:20px; margin-left:1px;" class="form-check-input">
                                        <label class="form-check-label m-1" for="selectAll">اختار الكل</label>
                                    </div>
                                </div>

                                {{-- @if ($rule_permission->id == 2)
                                        @foreach ($allpermission as $item)
                                        <div class="col-6 col-md-5 col-lg-4 my-2">
                                                <div class="form-check">
                                                    <input type="checkbox" id="exampleCheck{{ $item->id }}"
                                value="{{ $item->id }}" style="width: 20px; height:20px; margin-left:1px; "
                                name="permissions_ids[]" class="form-check-input selectPermission">

                                <label class="form-check-label m-1"
                                    for="exampleCheck{{ $item->id }}">{{__('permissions.' . $item->name)}}</label>
                            </div>
                        </div>
                        @endforeach
                        @else --}}
                                @php
                                    $hisPermissionIds = $hisPermissions->pluck('id')->toArray();
                                @endphp
                                <div class="form-row mx-md-2 d-flex justify-content-center text-right">
                                    <div class="form-group">
                                        <div class="row">



                                            <!-- Loop through grouped permissions (just like in add view) -->
                                            @foreach ($groupedPermissions as $group => $permissions)
                                                <div class="col-12 my-3">
                                                    <hr>
                                                    <h4 class="text-info">: {{ __('permissions.' . $group) }}</h4>
                                                    <!-- Group Title -->

                                                    <div class="d-flex flex-wrap justify-content-end gap-3">
                                                        @foreach ($permissions as $item)
                                                            <div class="form-check my-2">
                                                                <input type="checkbox"
                                                                    id="exampleCheckEdit{{ $item->id }}"
                                                                    value="{{ $item->id }}"
                                                                    name="permissions_ids[]"
                                                                    class="form-check-input selectPermission"
                                                                    style="width: 20px; height: 20px;"
                                                                    {{ in_array($item->id, $hisPermissionIds) ? 'checked' : '' }}>
                                                                <label class="form-check-label mx-2"
                                                                    for="exampleCheckEdit{{ $item->id }}"
                                                                    style="font-size: 20px;">
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

                                {{-- @endif --}}
                            </div>
                        </div>
                    </div>
            </div>


            <!-- Save button -->
            <div class="container col-10 ">
                <div class="form-row mt-4 mb-5">
                    <button type="submit" class="btn-blue">حفظ</button>
                </div>
            </div>
            </form>
        </div>
    </div>
    </div>

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
    // Listen for clicks on the checkboxes and labels
    document.querySelectorAll('.form-check').forEach(function(element) {
        // Handle click on the checkbox itself
        element.querySelector('.form-check-input').addEventListener('click', function(event) {
            handleCheckboxToggle(event.target);
        });

        // Handle click on the label
        element.querySelector('.form-check-label').addEventListener('click', function(event) {
            // Prevent the default action to allow manual control
            event.preventDefault();
            const checkbox = element.querySelector('.form-check-input');
            checkbox.checked = !checkbox.checked; // Toggle checkbox state manually
            handleCheckboxToggle(checkbox); // Call the function to handle additional logic if needed
        });
    });

    // Function to manage checkbox state (add additional logic if needed)
    function handleCheckboxToggle(checkbox) {
        if (!checkbox.checked) {
            // Custom behavior for unchecked state (optional)
            console.log(`Unchecked: ${checkbox.value}`);
        } else {
            // Custom behavior for checked state (optional)
            console.log(`Checked: ${checkbox.value}`);
        }
    }
</script>


@endsection
