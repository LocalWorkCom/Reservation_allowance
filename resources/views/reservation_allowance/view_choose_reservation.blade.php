<style>
.div-info {
    border-radius: 10px;
    padding: 20px;
    margin-top: 20px;
    width: 200px;
    height: 200px;
    background-color: #F6F7FD;
    border: 1px solid #D9D9D9 !important;
}

.div-info-padding {
    padding: 3px 0;
    direction: initial;
    font-family: Almarai;
    font-size: 24px;
    font-weight: 700;
    line-height: 36px;
    text-align: right;

}

.div-info-padding b span {
    color: #032F70;
}

.custom-select {
    width: 100%;
    color: green !important;
    border-radius: 10px !important;
    height: 43px !important;
    background-color: #fafbfd !important;
}

.custom-select-lg {
    /* height: calc(2.45rem + 0px) !important; */
    padding-top: 0.375rem;
    padding-bottom: .375rem;
    font-size: 125%;
    margin-inline: 5px !important;
}
</style>


@extends('layout.main')
@push('style')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
<script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js" defer></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer>
</script>
@endpush

@section('title')
القطاعات
@endsection
@section('content')
<div class="row">
    <div class="container welcome col-11" style="height: auto !important">

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


        <div class="d-flex justify-content-between">
            <div class="col-12">
                <div class=" d-flex flex-wrap justify-content-between " style="height: 40px;direction: rtl">
                    <div>
                        <p> بدل حجز بالهويات</p>
                    </div>

                    <?php /*<form class="" id="search_employee_allowances">
                        @csrf
                        <div class="row d-flex flex-wrap">
                            <!-- 1 for sector , 2 for department -->
                            <input name="department_type" id="department_type" type="hidden"
                                value="{{ Auth::user()->department_id == null ? 1 : 2 }}">

                                <div class="form-group  mx-2">
                                {{-- @if (Auth::user()->hasPermission('create reservation_allowances')) --}}
                                <!-- <label for="Civil_number" class="d-flex "> <i class="fa-solid fa-asterisk" style="color:red; font-size:10px;"></i>اختار </label> -->
                                <!-- <select class="custom-select custom-select-lg select2" name="sector_id" id="sector_id"
                                    required>
                                    <option value="0" selected>اختار القطاع</option>
                                    @foreach ($sectors as $sector)
                                    <option value="{{ $sector->id }}" {{$sector->id == $sector_id ? "selected" : ""}}>
                                        {{ $sector->name }}</option>
                                    @endforeach
                                </select> -->
                                <input class="form-control" type="text" name="sector_id" id="sector_id"
                                value="{{$current_sector ? $current_sector->name : ''}}" readonly>
                                </div>

                                <div class="form-group  mx-2">
                                {{-- @if (Auth::user()->hasPermission('create reservation_allowances')) --}}
                                <!-- <label for="Civil_number" class="w-75"> <i class="fa-solid fa-asterisk" style="color:red; font-size:10px;"></i>اختار الادارة</label> -->
                                <!-- <select class="custom-select custom-select-lg" name="departement_id"
                                    id="departement_id" style="    color: #7c7b7b !important;">
                                    <option value="0">اختار الادارة</option>
                                    @if($get_departements)
                                    @foreach($get_departements as $departement)
                                    <option value="{{ $departement->id }}"
                                        {{$departement->id == $department_id ? "selected" : ""}}>
                                        {{ $departement->name }}</option>
                                    @if(count($departement->children))
                                    @include('reservation_allowance.manageChildren', [
                                    'children' => $departement->children,
                                    'parent_id' => $department_id,
                                    ])
                                    @endif
                                    @endforeach
                                    @endif

                                </select> -->
                                <input class="form-control" type="text" name="departement_id" id="departement_id"
                                    value="{{$current_departement ? $current_departement->name : ''}}" readonly>
                            </div>

                            <div class="form-group  mx-2">
                                <input class="form-control" type="date" name="date" id="date" max="{{$to_day}}"
                                    value="{{$to_day}}" readonly>
                            </div>

                            <!-- <div class="">
                                        <button class="btn-all py-2 px-2" type="submit" style="color:green;">
                                            <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">
                                            عرض موظفين بدل حجز </button>
                                </div>-->
                        </div>
                    </form>*/?>
                    <!--  <div class="d-flex justify-content-between mt-2">
                                <div class=" mx-2">
                                    {{-- @if (Auth::user()->hasPermission('create reservation_allowances')) --}}
                                    <a class="btn-all py-2 px-2 " href="{{ route('reservation_allowances.create') }}"
                                        style="color: #0D992C;">
                                        <img src="{{ asset('frontend/images/add-btn.svg') }}" alt="img">
                                        اضافة بدل حجز جديد
                                    </a>
                                    {{-- @endif --}}
                                </div> -->

                </div>
                <!-- show_reservation_allowances_info -->
                <!-- <div id="show_reservation_allowances_info" class="col-12"></div> -->
                <!-- end of show_reservation_allowances_info -->
            </div>
        </div>
    </div>
</div>
</div>

<br>
<div class="row " dir="rtl">
    <div class="container col-11 p-4">
        <div class=" d-flex flex-wrap justify-content-between">
            <div class=" col-12 d-flex mb-4  ">
                @if($current_sector)
                <h5 class="text-dark mx-3">القطاع : <span class="text-info">{{$current_sector->name}}</span></h5>
                @endif
                @if($current_departement)
                <h5 class="text-dark mx-3">الادارة : <span class="text-info">{{$current_departement->name}}</span></h5>
                @endif

                <!-- <h5 class="text-dark">ملاحظات الملف : <span class="text-info">211</span></h5> -->
            </div>
            <div class=" col-5 d-flex mb-4  ">
                 <h5 class="text-dark mx-3">التاريخ : <span class="text-info">{{$date}}</span></h5>
                <h5 class="text-dark mx-3">القوة : <span class="text-info">{{count($get_employee_for_all_reservations) + count($get_employee_for_part_reservations)}}</span></h5>
                <h5 class="text-dark mx-3">التكلفة : <span class="text-info">{{$reservation_amount_all + $reservation_amount_part}}</span></h5>

                <!-- <h5 class="text-dark">ملاحظات الملف : <span class="text-info">211</span></h5> -->
            </div>
            <div class="col-5 d-flex align-items-end justify-content-end">
                <!-- <select class="form-select form-select-lg select2 w-50 mx-3" name="sector_id" id="sector_id" required>
                    <option selected disabled>وكيل الوزارة المساعد لشئون امن المنافذ</option>
                    <option>1</option>
                </select> -->
                <?php /*@if(Cache::get(auth()->user()->id."_employee_new_add") != null)*/?>
                @if(Cache::get(auth()->user()->id) != null)
                <div class="col-lg-6" style="text-align: right">
                        <input type="hidden" name="date" id="date" value="{{$date}}">
                        <input type="hidden" name="sector_id" id="sector_id" value="{{$sectorId}}">
                        <input type="hidden" name="departement_id" id="departement_id" value="{{$departementId}}">
                    <button class="btn btn-success py-2 px-3 mx-2" onclick="confirm_reservation()">اعتماد الكشف</button>
                </div>

                <div class="col-lg-6" style="text-align: right">
                    <button class="btn btn-success py-2 px-3 mx-2" onclick="printPDF1()">طباعة</button>
                </div>

                @endif
            </div>
        </div>

        <ul class="nav nav-tabs " id="myTab" role="tablist">
            <li class="nav-item " role="presentation ">
                <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button"
                    role="tab" aria-controls="home" aria-selected="true">
                    الموظفين الذين سيتم اضافة حجز كلى ( {{ $get_employee_for_all_reservations ? count($get_employee_for_all_reservations) : 0}} )
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link " id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button"
                    role="tab" aria-controls="profile" aria-selected="false">
                    الموظفين الذين سيتم اضافة حجز جزئى ( {{ $get_employee_for_part_reservations ? count($get_employee_for_part_reservations) : 0}} )
                </button>
            </li>
        </ul>

        <div class="tab-content mt-3" id="myTabContent">
            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                @if($get_employee_for_all_reservations)
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th style="width:5%">م</th>
                            <th>الرتبة</th>
                            <th>الاسم</th>
                            <th>رقم الملف</th>
                            <th>التكلفة</th>
                            <th>الادارة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($get_employee_for_all_reservations as $K_get_employee_for_all_reservation=>$get_employee_for_all_reservation)
                        <tr>
                            <td>{{$K_get_employee_for_all_reservation+1}}</td>
                            <td>{{$get_employee_for_all_reservation->grade != null ? $get_employee_for_all_reservation->grade->name : ""}}</td>
                            <td>{{$get_employee_for_all_reservation->name}}</td>
                            <td>{{$get_employee_for_all_reservation->file_number}}</td>
                            <td>{{$get_employee_for_all_reservation->grade_value}}</td>
                            <td>{{$get_employee_for_all_reservation->department_id != null ? $get_employee_for_all_reservation->department->name : ""}}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <h3 class="text-center text-info"> لا يوجد بيانات</h3>
                @endif
            </div>

            <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                @if($get_employee_for_part_reservations)
                <table class="table table-bordered table-hover">
                <thead>
                        <tr>
                            <th style="width:5%">م</th>
                            <th>الرتبة</th>
                            <th>الاسم</th>
                            <th>رقم الملف</th>
                            <th>التكلفة</th>
                            <th>الادارة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($get_employee_for_part_reservations as $K_get_employee_for_part_reservation=>$get_employee_for_part_reservation)
                        <tr>
                            <td>{{$K_get_employee_for_part_reservation+1}}</td>
                            <td>{{$get_employee_for_part_reservation->grade != null ? $get_employee_for_part_reservation->grade->name : ""}}</td>
                            <td>{{$get_employee_for_part_reservation->name}}</td>
                            <td>{{$get_employee_for_part_reservation->file_number}}</td>
                            <td>{{$get_employee_for_part_reservation->grade_value}}</td>
                            <td>{{$get_employee_for_part_reservation->department_id != null ? $get_employee_for_part_reservation->department->name : ""}}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <h3 class="text-center text-info"> لا يوجد بيانات</h3>
                @endif
            </div>
            
            
        </div> 

    </div>

</div>

@endsection
@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirm_reservation() {
    Swal.fire({
        title: 'تنبيه',
        text: 'هل انت متاكد من انك تريد ان تضيف بدل حجز لهؤلاء الموظفين',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'نعم, اعتمد',
        cancelButtonText: 'إلغاء',
        confirmButtonColor: '#3085d6'
    }).then((result) => {
        if (result.isConfirmed) {
            var reservation_date = document.getElementById('date').value;
            var reservation_sector_id = document.getElementById('sector_id').value;
            var reservation_departement_id = document.getElementById('departement_id').value;
            var map_url = "{{ route('reservation_allowances.confirm_reservation_allowances', ['date', 'sector', 'departement']) }}";
            map_url = map_url.replace('date', reservation_date);
            map_url = map_url.replace('sector',reservation_sector_id);
            map_url = map_url.replace('departement',reservation_departement_id);
            window.location.href = map_url;
        } else {

        }
    });
}

function printPDF() {
    var reservation_date = document.getElementById('date').value;
    var reservation_sector_id = document.getElementById('sector_id').value;
    var reservation_departement_id = document.getElementById('departement_id').value;
    var map_url = "{{ route('reservation_allowances.print', ['date', 'sector', 'departement']) }}";
    map_url = map_url.replace('date', reservation_date);
    map_url = map_url.replace('sector',reservation_sector_id);
    map_url = map_url.replace('departement',reservation_departement_id);
    window.location.href = map_url;
    if (fileNumber) {
        window.open('{{ route('reservation_fetch.print') }}?file_number=' + fileNumber, '_blank');
    } else {
        alert('Please enter a valid File Number');
    }
 }
</script>

<script>
//     import {
//     Tab,
//     initMDB
// } from "mdb-ui-kit";

// initMDB({
//     Tab
// });
</script>
@endpush