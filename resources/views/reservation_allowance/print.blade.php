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
                <th style="width:5%">م</th>
                <th style="width:20%">الرتبة</th>
                <th style="width:30%">الاسم</th>
                <th style="width:10%">رقم الملف</th>
                <th style="width:20%">الادارة</th>
                <th style="width:15%">القيمة</th>
            </tr>
        </thead>
         <tbody>
            @foreach ($get_employee_for_all_reservations as $index => $get_employee_for_all_reservation)
                <tr>
                    <td style="width:5%">{{ $index + 1 }}</td>
                    <td style="width:20%">{{ $get_employee_for_all_reservation->grade != null ? $get_employee_for_all_reservation->grade->name : "" }}</td>
                    <td style="width:30%">{{ $get_employee_for_all_reservation->name }}</td>
                    <td style="width:10%">{{ $get_employee_for_all_reservation->file_number }}</td>
                    <td style="width:20%">{{ $get_employee_for_all_reservation->department != null ? $get_employee_for_all_reservation->department->name : "" }}</td>
                    <td style="width:15%">{{ $get_employee_for_all_reservation->grade_value }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="font-weight: bold; text-align: center;">المجموع الكلي</td>
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
                <th style="width:5%">م</th>
                <th style="width:20%">الرتبة</th>
                <th style="width:30%">الاسم</th>
                <th style="width:10%">رقم الملف</th>
                <th style="width:20%">الادارة</th>
                <th style="width:15%">القيمة</th>
            </tr>
        </thead>
         <tbody>
            @foreach ($get_employee_for_part_reservations as $index => $get_employee_for_part_reservation)
                <tr>
                    <td style="width:5%">{{ $index + 1 }}</td>
                    <td style="width:20%">{{ $get_employee_for_part_reservation->grade != null ? $get_employee_for_part_reservation->grade->name : "" }}</td>
                    <td style="width:30%">{{ $get_employee_for_part_reservation->name }}</td>
                    <td style="width:10%">{{ $get_employee_for_part_reservation->file_number }}</td>
                    <td style="width:20%">{{ $get_employee_for_part_reservation->department != null ? $get_employee_for_part_reservation->department->name : "" }}</td>
                    <td style="width:15%">{{ $get_employee_for_part_reservation->grade_value }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="font-weight: bold; text-align: center;">المجموع الكلي</td>
                <td colspan="1" style="font-weight: bold; text-align: center;">
                    {{ number_format((float) $reservation_amount_part, 2) }}
                </td>          
            </tr>
        </tfoot>
    </table>
    @endif
</body>
</html>
