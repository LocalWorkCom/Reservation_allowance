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
        h2, h3, p {
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Logo and Report Title -->
    <img src="{{ asset('img/logo.png') }}" alt="Logo" width="50px">
    <h2>تفاصيل بدل حجز لموظفي قطاع {{ $sector->name }}</h2>
    <p><strong>من تاريخ:</strong> {{ $startDate->format('Y-m-d') }} <strong>إلى تاريخ:</strong> {{ $endDate->format('Y-m-d') }}</p>
    
    <!-- Summary -->
    <p><strong>عدد الموظفين:</strong> {{ $reservations->unique('user_id')->count() }}</p>
    <p><strong>إجمالي المبلغ:</strong> {{ number_format($reservations->sum('amount'), 2) }} د.ك</p>

    <!-- Details Table -->
    <table>
        <thead>
            <tr>
                <th>الترتيب</th>
                <th>اليوم</th>
                <th>التاريخ</th>
                <th>اسم الموظف</th>
                <th>رقم الملف</th> 
                <th>الرتبة</th> 
                <th>الإدارة</th>
                <th>نوع الحجز</th>
                <th>المبلغ</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reservations as $index => $reservation)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($reservation->date)->translatedFormat('l') }}</td>
                    <td>{{ \Carbon\Carbon::parse($reservation->date)->format('Y-m-d') }}</td>
                    <td>{{ optional($reservation->user)->name ?? 'غير معروف' }}</td>
                    <td>{{ optional($reservation->user)->file_number ?? 'N/A' }}</td> 
                    <td>{{ optional($reservation->user->grade)->name ?? 'N/A' }}</td> 
                    <td>{{ optional($reservation->user->department)->name ?? 'N/A' }}</td>
                    <td>{{ $reservation->type == 1 ? 'حجز كلي' : 'حجز جزئي' }}</td>
                    <td>{{ number_format($reservation->amount, 2) }} د.ك</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
