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
    <h3>تفاصيل الموظفين المحجوزين في الإدارة الرئيسية: {{ $department->name }}</h3>
    <p><strong>الفترة من:</strong> {{ $startDate->format('Y-m-d') }} <strong>إلى:</strong> {{ $endDate->format('Y-m-d') }}</p>

    <table>
        <thead>
            <tr>
                <th rowspan="2">الترتيب</th>
                <th rowspan="2">الرتبة</th>
                <th rowspan="2">الاسم</th>
                <th rowspan="2">رقم الملف</th>
                <th colspan="3">أيام الحجز</th>
                <th colspan="3">المبلغ</th>
            </tr>
            <tr>
            <th>كلي </th>
            <th>جزئي </th>
            <th>إجمالي </th>
            <th>بدل الحجز (كلي)</th>
            <th>بدل الحجز (جزئي)</th>
            <th>إجمالي بدل الحجز</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $index => $employee)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $employee['grade'] }}</td>
                    <td>{{ $employee['name'] }}</td>
                    <td>{{ $employee['file_number'] }}</td>
                    <td>{{ $employee['full_days'] }}</td>
                    <td>{{ $employee['partial_days'] }}</td>
                    <td>{{ $employee['total_days'] }}</td>
                    <td>{{ $employee['full_allowance'] }} د.ك</td>
                    <td>{{ $employee['partial_allowance'] }} د.ك</td>
                    <td>{{ $employee['total_allowance'] }} د.ك</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
