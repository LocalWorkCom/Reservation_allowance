<form action="{{ route('reservation_allowances.check_store') }}" id="add_create_all" method="post" class="text-right" enctype="multipart/form-data">
    @csrf

    <input type="hidden" name="sector_id" value="{{ $sector_id }}">
    <input type="hidden" name="departement_id" value="{{ $department_id }}">

    @if(Auth::user()->rule_id == 2)
    <div class="form-group col-md-2 mx-2">
        <label for="date"> <i class="fa-solid fa-asterisk" style="color:red; font-size:10px;"></i>
            اختار التاريخ</label>
        <input class="form-control" type="date" name="date" id="date" max="{{$today}}" value="{{$today}}" required>
    </div>
    @else
    <input class="form-control" type="hidden" name="date" id="date" value="{{$today}}" required>
    @endif

    <div class="form-group col-md-12 mx-2">
        <label for="Civil_number"> <i class="fa-solid fa-asterisk"
                style="color:red; font-size:10px;"></i>
            ادخل ارقام ملفات مستحقى بدل حجز لهذا اليوم</label>
        <textarea class="form-control" name="Civil_number" id="Civil_number" style="height: 200px !important"></textarea>
    </div>

    <div class="form-group col-md-12 mx-2">
        <label for="type">صلاحية الحجز</label>
        <div class="d-flex justify-content-end">
            @if ($reservation_allowance_type == 1 || $reservation_allowance_type == 3)
                <div class="d-flex justify-content-end">
                    <label for=""> حجز كلى</label>
                    <input type="radio" id="type" name="type" class="form-control" checked
                        value="1" required>
                </div>
            @endif
            @if ($reservation_allowance_type == 2 || $reservation_allowance_type == 3)
                <div class="d-flex justify-content-end mx-4">
                    <label for=""> حجز جزئى</label>
                    <input type="radio" id="type" name="type" class="form-control" {{$reservation_allowance_type == 2 ? "checked" : ""}}
                        value="2" required>
                </div>
            @endif
            @if ($reservation_allowance_type == 4)
                <div class="d-flex justify-content-end mx-4">
                    <label for=""> لا يوجد بدل حجز</label>
                </div>
            @endif
        </div>
        <span class="text-danger span-error" id="type-error" dir="rtl"></span>
    </div>

    @if ($reservation_allowance_type != 4)
    <div class="container col-12 mt-3 mb-3 ">
        <div class="form-row col-10 " dir="ltr">
            <button class="btn-blue" type="submit">
                اضافة </button>
        </div>
    </div>
    @endif
</from>