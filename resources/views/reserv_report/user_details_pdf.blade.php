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
        h3, p {
            text-align: center;
        }
        .text-info {
            color: #017BFF; /* Adjust to match your branding */
        }
    </style>
</head>
<body>
    <img src="{{ asset('img/logo.png') }}" alt="Logo" width="50px">
    <h3>
        تفاصيل الحجز للموظف: 
        <span class="text-info">{{ $latestGrade }} / {{ $user->name }}</span>
        <br>
        <span>قطاع: <span class="text-info">{{ $sectorName }}</span></span>
    </h3>
    <p><strong>الفترة من:</strong> {{ $startDate->format('Y-m-d') }} <strong>إلى:</strong> {{ $endDate->format('Y-m-d') }}</p>

    <table>
        <thead>
            <tr>
                <th>الترتيب</th>
                <th>اليوم</th>
                <th>التاريخ</th>
                <th>الرتبة</th>
                <th>نوع الحجز</th>
                <th>المبلغ</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($mappedReservations as $index => $reservation)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $reservation['day'] }}</td>
                <td>{{ $reservation['date'] }}</td>
                <td>{{ $reservation['grade'] }}</td>
                <td>{{ $reservation['type'] }}</td>
                <td>{{ $reservation['amount'] }} د.ك</td>
            </tr>
        @empty
            <tr>
                <td colspan="6">لا توجد بيانات متاحة للفترة المحددة.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
