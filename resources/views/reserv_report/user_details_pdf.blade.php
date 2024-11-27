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
    </style>
</head>
<body>
    <img src="{{ asset('img/logo.png') }}" alt="Logo" width="50px">
    <h3>تفاصيل الحجز للموظف: {{ $user->name }}</h3>
    <p><strong>الفترة من:</strong> {{ $startDate->format('Y-m-d') }} <strong>إلى:</strong> {{ $endDate->format('Y-m-d') }}</p>

    <table>
        <thead>
            <tr>
                <th>الترتيب</th>
                <th>اليوم</th>
                <th>التاريخ</th>
                <th>نوع الحجز</th>
                <th>المبلغ</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reservations as $index => $reservation)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $reservation['day'] }}</td>
                    <td>{{ $reservation['date'] }}</td>
                    <td>{{ $reservation['type'] }}</td>
                    <td>{{ $reservation['amount'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
