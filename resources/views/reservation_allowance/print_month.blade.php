<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'dejavusans';
            direction: rtl;
            text-align: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        h2 {
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header-box">
        <img src="{{ asset('img/logo.png') }}" alt="Logo" width="50px">
        <h2>بدل حجز </h2>
        @if($month)<p>شهر: {{ $month }}</p>@endif
        @if($year)<p>سنة: {{ $year }}</p>@endif
        @if($sector)<p>القطاع: {{ $sector }}</p>@endif
        @if($department)<p>الادارة: {{ $department }}</p>@endif
    </div>

    @if($get_employee_reservations)
    <h2>بدل حجز  </h2>
    <table>
    <thead>
            <tr>
                <th style="width:5%">م</th>
                <th style="width:15%">الرتبة</th>
                <th style="width:30%">الاسم</th>
                <th style="width:10%">رقم الملف</th>
                <th style="width:20%">الادارة او القطاع</th>
                <th style="width:10%">نوع بدل الحجز</th>
                <th style="width:10%">القيمة</th>
            </tr>
        </thead>
         <tbody>
            @foreach ($get_employee_reservations as $index => $get_employee_reservation)
                <tr>
                    <td style="width:5%">{{ $index + 1 }}</td>
                    <td style="width:15%">{{ $get_employee_reservation->user->grade != null ? $get_employee_reservation->user->grade->name : "" }}</td>
                    <td style="width:30%">{{ $get_employee_reservation->user->name }}</td>
                    <td style="width:10%">{{ $get_employee_reservation->user->file_number }}</td>
                    <td style="width:20%">{{ $get_employee_reservation->user->department_id != null ? $get_employee_reservation->user->department->name : $get_employee_reservation->user->sectors->name }}</td>
                    <td style="width:10%">{{ $get_employee_reservation->type == 1 ? "كلى" : "جزئى" }}</td>
                    <td style="width:10%">{{ $get_employee_reservation->amount }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" style="font-weight: bold; text-align: center;">المجموع الكلي</td>
                <td colspan="1" style="font-weight: bold; text-align: center;">
                    {{ number_format((float) $reservation_amount, 2) }}
                </td>          
            </tr>
        </tfoot>
    </table>
    @endif

</body>
</html>
