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
        <h2>تقرير الحجز</h2>
        <p>اسم المستخدم: {{ $user->name }}</p>
        <p>رقم الهوية: {{ $user->Civil_number }}</p>
        <p>القطاع: {{ $sector }}</p>
        <p>الإدارة: {{ $department }}</p>
        <p>الرتبة: {{ $grade }}</p>
        <p>نوع الرتبة: {{ $gradeType }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>الترتيب</th>
                <th>اليوم</th>
                <th>التاريخ</th>
                <th>نوع الحجز</th>
                <th>القيمة</th>
                <th>الادارة</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reservations as $index => $reservation)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($reservation->date)->translatedFormat('l') }}</td>
                    <td>{{ \Carbon\Carbon::parse($reservation->date)->format('Y-m-d') }}</td>
                    <td>{{ $reservation->type == 1 ? 'حجز كلي' : 'حجز جزئي' }}</td>
                    <td>{{ number_format($reservation->amount, 2) }}</td>
                    <td>{{ $reservation->departements->name ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="font-weight: bold; text-align: center;">المجموع الكلي</td>
                <td colspan="2" style="font-weight: bold; text-align: center;">{{ number_format($totalAmount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="4" style="font-weight: bold; text-align: center;">المجموع للحجز الكلي</td>
            <td colspan="2" style="font-weight: bold; text-align: center;"> {{ number_format($totalFullReservation, 2) }}</td>
        </tr>

        <tr><td colspan="4" style="font-weight: bold; text-align: center;">المجموع للحجز الجزئي</td>
            <td colspan="2" style="font-weight: bold; text-align: center;">  {{ number_format($totalPartialReservation, 2) }}</td>
        </tr>
        </tfoot>
    </table>
</body>
</html>
