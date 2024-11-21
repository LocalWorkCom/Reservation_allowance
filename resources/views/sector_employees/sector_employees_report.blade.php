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
    <img src="{{ asset('img/logo.png') }}" alt="Logo" width="50px"> 
    <h2>تقرير بدل حجز لقطاع  {{ $sector->name }} - {{ $month }}/{{ $year }}</h2>

    <table>
    <thead>
    <tr>
        <th>الترتيب</th>
        <th>رقم الملف</th> 
        <th>اسم الموظف</th>
        <th>الإدارة</th>
        <th>الرتبة</th>
        <th>الأيام</th>
        <th>مبلغ الحجز</th>
    </tr>
</thead>
<tbody>
    @foreach($userReservations as $index => $reservation)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $reservation['file_number'] }}</td> 
            <td>{{ $reservation['user']->name }}</td>
            <td>{{ $reservation['department'] }}</td>
            <td>{{ $reservation['grade'] }}</td>
            <td>كلي: {{ $reservation['fullDays'] }} | جزئي: {{ $reservation['partialDays'] }} | مجموع: {{ $reservation['totalDays'] }}</td>
            <td>كلي: {{ number_format($reservation['fullAllowance'], 2) }} د.ك | جزئي: {{ number_format($reservation['partialAllowance'], 2) }} د.ك | مجموع: {{ number_format($reservation['totalAllowance'], 2) }} د.ك</td>
        </tr>
    @endforeach
</tbody>

    </table>
</body>
</html>
