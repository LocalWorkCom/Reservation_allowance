<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تفاصيل بدل حجز لموظفي إدارة {{ $department->name }}</title>
</head>
<body>
<img src="{{ asset('img/logo.png') }}" alt="Logo" width="50px"> 

    <h2 style="text-align: center;">تفاصيل بدل حجز لموظفي إدارة {{ $department->name }}</h2>
    <p style="text-align: center;">الفترة من: {{ $startDate->format('Y-m-d') }} إلى: {{ $endDate->format('Y-m-d') }}</p>

    <table border="1" cellpadding="5" cellspacing="0" style="width: 100%; text-align: center;">
        <thead>
            <tr>
                <th>الترتيب</th>
                <th>اليوم</th>
                <th>التاريخ</th>
                <th>الاسم</th>
                <th>الإدارة</th>
                <th>النوع</th>
                <th>المبلغ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservations as $index => $reservation)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($reservation->date)->translatedFormat('l') }}</td>
                    <td>{{ \Carbon\Carbon::parse($reservation->date)->format('Y-m-d') }}</td>
                    <td>{{ $reservation->user->name ?? 'غير معروف' }}</td>
                    <td>{{ $reservation->user->department->name ?? 'غير معروف' }}</td>
                    <td>{{ $reservation->type == 1 ? 'حجز كلي' : 'حجز جزئي' }}</td>
                    <td>{{ number_format($reservation->amount, 2) }} د.ك</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
