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
<img src="{{ asset('img/logo.png') }}" alt="Logo" width="50px"> 

    <h3> الموظفين للإدارة الفرعية: {{ $subDepartment->name }}</h3>
    <p>الفترة من: {{ $startDate->format('Y-m-d') }} إلى: {{ $endDate->format('Y-m-d') }}</p>

    
           
    <table>
        <thead>
            <tr>
                <th>الترتيب</th>
                <th>اليوم</th>
                <th>التاريخ</th>
                <th>الرتبة</th>
                <th>الاسم</th>
                <th>رقم الملف</th>
                
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
                    <td>{{ $employee['grade'] }}</td>
                    <td>{{ $employee['name'] }}</td>
                    <td>{{ $employee['file_number'] }}</td>
                   
                    <td>{{ $employee['type'] }}</td>
                    <td>{{ $employee['amount'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
