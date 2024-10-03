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
                    <td>{{ $reservation->departements->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
