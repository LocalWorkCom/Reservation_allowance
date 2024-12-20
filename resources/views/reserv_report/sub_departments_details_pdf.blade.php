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

    <h3>تفاصيل الإدارات الفرعية للإدارة الرئيسية : <span class="text-info">{{ $mainDepartment->name }}</span></h3>
    <p>الفترة من: {{ $startDate->format('Y-m-d') }} إلى: {{ $endDate->format('Y-m-d') }}</p>

    <table>
        <thead>
            <tr>
                <th>الترتيب</th>
                <th>اسم الإدارة الفرعية</th>
                <th>عدد الموظفين</th>
                <th>مبلغ الحجز</th> 
            </tr>
        </thead>
        <tbody>
            @foreach ($subDepartments as $index => $department)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $department['sub_department_name'] }}</td>
                    <td>{{ $department['employee_count'] }}</td>
                    <td>{{ $department['reservation_amount'] }}</td> 
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
