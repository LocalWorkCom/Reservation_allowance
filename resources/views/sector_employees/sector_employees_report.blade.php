<!DOCTYPE html>
<html lang="en">
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
 <h2>تقرير بدل حجز لقطاع  {{ $sector->name }}</h2>

 @foreach($userReservations as $reservation)
    <div style="margin-bottom: 20px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
        <h3 style="margin-bottom: 5px;">الاسم: {{ $reservation['user']->name }}</h3>
        <p><strong>الرتبة:</strong> {{ $reservation['user']->grade->name ?? 'غير متوفر' }}</p>

        <p><strong>الأيام:</strong> 
            <span>كلي: {{ $reservation['fullDays'] }} | جزئي: {{ $reservation['partialDays'] }} | المجموع: {{ $reservation['totalDays'] }}</span>
        </p>

        <p><strong>بدل الحجز:</strong> 
            <span>كلي: {{ number_format($reservation['fullAllowance'], 2) }} د.ك | جزئي: {{ number_format($reservation['partialAllowance'], 2) }} د.ك | المجموع: {{ number_format($reservation['totalAllowance'], 2) }} د.ك</span>
        </p>
    </div>
@endforeach
</body>
</html>
