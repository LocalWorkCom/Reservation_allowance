@extends('layout.main')

@section('content')

    <div class="row " dir="rtl">
        <div class="container  col-11" style="background-color:transparent;">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item "><a href="{{ route('home') }}">الرئيسيه</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('departments.index', $department->sectors->uuid) }}">الادارات
                        </a></li>
                    <li class="breadcrumb-item active" aria-current="page"> <a href="#">تفاصيل الادارة</a></li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="row ">
        <div class="container welcome col-11">
            <p> الادارات </p>
        </div>
    </div>
    <section style="direction: rtl;">
        <div class="row">
            <div class="container  col-12 mt-3 p-0 col-md-11 col-lg-11 col-s-11 pt-5 pb-4 px-3">
                <table class="table  table-bordered ">
                    <tbody>
                        <tr>
                            <th scope="row" style="background: #f5f6fa;">اسم الادارة</th>
                            <td style="background: #f5f6fa;">{{ $department->name }}</td>
                        </tr>
                        <tr>
                            <th scope="row">المدير</th>
                            <td>{{ $department->manager ? $department->manager->name : 'N/A' }}</td>
                        </tr>

                        <tr>
                            <th scope="row">ميزانية البدل</th>
                            <td>{{ $department->reservation_allowance_amount == 0.00 ? 'ميزانيه غير محددة' :  $department->reservation_allowance_amount }}</td>
                        </tr>
                        <tr>
                            <th scope="row">صلاحيه الحجز</th>
                            @if ($department->reservation_allowance_type == 1)
                            <td>بدل حجز كلى </td>

                            @elseif($department->reservation_allowance_type == 2)
                            <td>بدل حجز جزئى</td>

                            @elseif($department->reservation_allowance_type == 3)
                            <td>بدل حجز كلى و جزئى </td>

                            @elseif($department->reservation_allowance_type == 4)
                            <td>لا يوجد بدل حجز </td>

                            @endif
                        </tr>
                        @if ($department->children->count() > 0)
                            @foreach ($department->children as $child)
                                <tr>

                                    <th scope="row"> القسم الفرعي </th>
                                    <td>{{ $child->name }} </td>

                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <th scope="row"> القسم الفرعي </th>
                                <td>لا يوجد </td>
                            </tr>
                        @endif

                    </tbody>
                </table>
            </div>
        </div>
        </div>
        </div>
        <br> <br> <br> <br>


        <?php /*<div class="container">
            <h1>Department Details</h1>

            <div class="card">
                <div class="card-header">
                    Department #{{ $department->id }}
                </div>
                <div class="card-body">
                    <h5 class="card-title">{{ $department->name }}</h5>
                    <p class="card-text"><strong>Manager:</strong> {{ $department->manager ? $department->manager->id : 'N/A' }}</p>
                    <p class="card-text"><strong>Manager Assistant:</strong> {{ $department->managerAssistant ? $department->managerAssistant->id : 'N/A' }}</p>
                    <p class="card-text"><strong>Description:</strong> {{ $department->description }}</p>
                    <p><strong>Parent Department:</strong> {{ $department->parent ? $department->parent->name : 'None' }}</p>

                </div>
            </div>
            <h2>Child Departments</h2>
            @if ($department->children->count() > 0)
    <ul>
                    @foreach ($department->children as $child)
    <li>{{ $child->name }}</li>
    @endforeach
                </ul>
@else
    <p>No child departments.</p>
    @endif
            <a href="{{ route('departments.index', ['id' => $department->id]) }}" class="btn btn-primary mt-3">Back to Departments</a>
        </div> */?>
    </section>
@endsection
