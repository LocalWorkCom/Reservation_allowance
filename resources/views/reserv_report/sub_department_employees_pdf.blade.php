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
    </style>
</head>
<body>
    <h3 style="text-align: center;">تفاصيل الموظفين للإدارة الفرعية: {{ $subDepartment->name }}</h3>
    <p><strong>من تاريخ:</strong> {{ $startDate->format('Y-m-d') }} <strong>إلى تاريخ:</strong> {{ $endDate->format('Y-m-d') }}</p>
    
    <table>
        <thead>
            <tr>
                <th>الترتيب</th>
                <th>اليوم</th>
                <th>التاريخ</th>
                <th>الاسم</th>
                <th>رقم الملف</th>
                <th>الدرجة</th>
                <th>نوع الحجز</th>
                <th>المبلغ</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $index => $employee)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $employee['day'] }}</td>
                    <td>{{ $employee['date'] }}</td>
                    <td>{{ $employee['name'] }}</td>
                    <td>{{ $employee['file_number'] }}</td>
                    <td>{{ $employee['grade'] }}</td>
                    <td>{{ $employee['type'] }}</td>
                    <td>{{ $employee['amount'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
