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
        @if($date)<p>التاريخ: {{ $date }}</p>@endif
        @if($sector)<p>القطاع: {{ $sector }}</p>@endif
        @if($department)<p>الادارة: {{ $department }}</p>@endif
    </div>

    @if($get_employee_for_all_reservations)
    <h2>بدل حجز كلى </h2>
    <table>
        <thead>
            <tr>
                <th style="width:10%">م</th>
                <th style="width:70%">الاسم</th>
                <th style="width:20%">القيمة</th>
            </tr>
        </thead>
         <tbody>
            @foreach ($get_employee_for_all_reservations as $index => $get_employee_for_all_reservation)
                <tr>
                    <td style="width:10%">{{ $index + 1 }}</td>
                    <td style="width:70%">{{ $get_employee_for_all_reservation->name }}</td>
                    <td style="width:20%">{{ $get_employee_for_all_reservation->grade_value }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="font-weight: bold; text-align: center;">المجموع الكلي</td>
                <td colspan="1" style="font-weight: bold; text-align: center;">
                    {{ number_format((float) $reservation_amount_all, 2) }}
                </td>          
            </tr>
        </tfoot>
    </table>
    @endif

    @if($get_employee_for_part_reservations)
    <h2>بدل حجز جزئى </h2>
    <table>
        <thead>
            <tr>
                <th style="width:10%">م</th>
                <th style="width:70%">الاسم</th>
                <th style="width:20%">القيمة</th>
            </tr>
        </thead>
         <tbody>
            @foreach ($get_employee_for_part_reservations as $index => $get_employee_for_part_reservationss)
                <tr>
                    <td style="width:10%">{{ $index + 1 }}</td>
                    <td style="width:70%">{{ $get_employee_for_part_reservationss->name }}</td>
                    <td style="width:20%">{{ $get_employee_for_part_reservationss->grade_value }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="font-weight: bold; text-align: center;">المجموع الكلي</td>
                <td colspan="1" style="font-weight: bold; text-align: center;">
                    {{ number_format((float) $reservation_amount_part, 2) }}
                </td>          
            </tr>
        </tfoot>
    </table>
    @endif
</body>
</html>
