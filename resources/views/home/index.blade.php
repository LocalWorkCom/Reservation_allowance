@extends('layout.main')
@section('title')
    الرئيسيه
@endsection
@section('content')
    <div class="row ">
        <div class="container welcome col-11">
            <p> {{ $userGrade }} / <span class="text-info">{{ auth()->user()->name }} </span> مرحـــــــــــــــبا بك </p>
        </div>

    </div>
    <br>
    <div class="row">

        <div class="container col-11">
            <div class="row justify-content-center p-4">
                <!-- First Row with 2 Cards -->

                <div class="col-md-6 col-12  ">
                    @if ($main && Auth::user()->rule->id != 3)
                        <div class="card2 col-12 mb-4 d-flex justify-content-between align-items-center px-5"
                            style="background-color:#DCFCE7;">
                            <div class="details">
                                <p>القطاعات</p>
                                <p>{{ $sectorCount }}</p>
                            </div>
                            <div class="card-imgg">
                                <img src="{{ asset('frontend/images/statistics.png') }}" alt="">
                            </div>
                        </div>
                    @endif
                    <div class="card3 col-12 mb-4  d-flex justify-content-between align-items-center px-5"
                        style="background-color:#E8F0FF;">
                        <div class="details">
                            <p> الادارات الفرعية</p>
                            <p>{{ $depChiledCount }}</p>
                        </div>
                        <div class="card-imgg">
                            <img src="{{ asset('frontend/images/management.png') }}" alt="">
                        </div>
                    </div>


                </div>
                <div class="col-md-6 col-12  ">
                    <div class="card1 col-12 mb-4  d-flex justify-content-between align-items-center px-5"
                        style="background-color:#FFF4DE;">
                        <div class="details">
                            <p>الموظفين</p>
                            <p>{{ $empCount }}</p>
                        </div>
                        <div class="card-imgg">
                            <img src="{{ asset('frontend/images/division.png') }}" alt="">
                        </div>
                    </div>
                    @if ($main)
                        <div class="card3 col-12 mb-4 d-flex justify-content-between align-items-center px-5"
                            style="background-color:#F3E8FF;">
                            <div class="details">
                                <p>الادارات الرئيسية </p>
                                <p>{{ $depMainCount }}</p>
                            </div>
                            <div class="card-imgg">
                                <img src="{{ asset('frontend/images/team-management.png') }}" alt="">
                            </div>
                        </div>
                    @endif



                </div>
                <!-- Second Row with 2 Cards -->

            </div>
        </div>





    </div>
@endsection
