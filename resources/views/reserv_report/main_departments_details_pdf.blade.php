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

    <h3>تفاصيل الإدارات الرئيسية للقطاع: {{ $sector->name }}</h3>
    <p>الفترة من: {{ $startDate->format('Y-m-d') }} إلى: {{ $endDate->format('Y-m-d') }}</p>

    <table>
        <thead>
            <tr>
                <th>الترتيب</th>
                <th>اسم الإدارة الرئيسية</th>
                <th>عدد الإدارات الفرعية</th>
                <th>عدد الموظفين</th>
                <th>مبلغ الحجز</th> 
            </tr>
        </thead>
        <tbody>
            @foreach ($mainDepartments as $index => $department)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $department['department_name'] }}</td>
                    <td>{{ $department['sub_departments_count'] }}</td>
                    <td>{{ $department['employee_count'] }}</td>
                    <td>{{ $department['reservation_amount'] }}</td> 
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
