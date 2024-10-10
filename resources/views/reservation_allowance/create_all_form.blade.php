<form action="{{ route('reservation_allowances.store.all') }}" id="add_create_all" method="post" class="text-right" enctype="multipart/form-data">
    @csrf

    <input type="hidden" name="sector_id" value="{{ $sector_id }}">
    <input type="hidden" name="departement_id" value="{{ $department_id }}">

    <div class="form-group col-md-2 mx-2">
        <label for="date"> <i class="fa-solid fa-asterisk" style="color:red; font-size:10px;"></i>
            اختار التاريخ</label>
        <input class="form-control" type="date" name="date" id="date" max="{{$today}}" value="{{$today}}" required>
    </div>

    <div class="form-group col-md-12 mx-2">
        <label for="Civil_number"> <i class="fa-solid fa-asterisk"
                style="color:red; font-size:10px;"></i>
            رقم الهوية</label>
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
        </div>
        <span class="text-danger span-error" id="type-error" dir="rtl"></span>
    </div>

    <div class="container col-12 mt-3 mb-3 ">
        <div class="form-row col-10 " dir="ltr">
            <button class="btn-blue " type="button" id="get_check_sector_department">
                اضافة </button>
        </div>
    </div>
</from>