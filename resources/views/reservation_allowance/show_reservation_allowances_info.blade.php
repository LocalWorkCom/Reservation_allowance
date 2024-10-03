    <div class="col-12 div-info d-flex justify-content-between">
        <div class="col-7">
            <div class="col-6 div-info-padding"><b>القطاع : <span
                        style="color:#032F70;">{{auth()->user()->department->sectors->count() > 0 ? auth()->user()->department->sectors->name : "لا يوجد"}}</span></b>
            </div>
            <div class="col-6 div-info-padding"><b>الادارة الفرعية : <span
                        style="color:#032F70;">{{auth()->user()->department->count() > 0 ? auth()->user()->department->name : "لا يوجد"}}</span></b></div>
            <div class="col-6 div-info-padding"><b>اليوم : <span style="color:#032F70;">
                        {{$to_day_name}}</span></b></div>
            <div class="col-6 div-info-padding"><b>عدد العسكرين المحجوزين : <span
                        style="color:#032F70;">{{$reservation_allowances->count()}}</span></b></div>
        </div>
        <div class="col-5">
            <div class="col-6 div-info-padding"><b>الادارة الرئيسية : <span
                        style="color:#032F70;">{{auth()->user()->department->parent == null ? $super_admin->name : auth()->user()->department->parent->name}}</span></b>
            </div>
            <div class="col-6 div-info-padding"><b>مبلغ بدل الحجز : <span
                        style="color:#032F70;">{{auth()->user()->department->count() > 0 ? auth()->user()->department->reservation_allowance_amount : "لا يوجد"}}</span></b>
            </div>
            <div class="col-6 div-info-padding"><b>التاريخ : <span style="color:#032F70;">
                        {{$to_day}}</span></b></div>
        </div>
    </div>