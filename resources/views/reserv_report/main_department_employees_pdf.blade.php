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
        h3 {
            text-align: center;
        }
    </style>
</head>
<body>
    <img src="{{ asset('img/logo.png') }}" alt="Logo" width="50px">
    <h3>تفاصيل الموظفين المحجوزين في الإدارة الرئيسية: {{ $department->name }}</h3>
    <p><strong>الفترة من:</strong> {{ $startDate->format('Y-m-d') }} <strong>إلى:</strong> {{ $endDate->format('Y-m-d') }}</p>

    <table>
        <thead>
            <tr>
                <th>الترتيب</th>
                <th>اسم الموظف</th>
                <th>الرقم المدني</th>
                <th>رقم الملف</th>
                <th>الرتبة</th>
                <th>مبلغ الحجز</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $index => $employee)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $employee['name'] }}</td>
                    <td>{{ $employee['civil_id'] }}</td>
                    <td>{{ $employee['file_number'] }}</td>
                    <td>{{ $employee['grade'] }}</td>
                    <td>{{ $employee['reservation_amount'] }} د.ك</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
