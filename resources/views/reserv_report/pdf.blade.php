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

    <h3>تقارير بدل حجز</h3>
    <p><strong>من تاريخ:</strong> {{ $startDate->format('Y-m-d') }} <strong>إلى تاريخ:</strong> {{ $endDate->format('Y-m-d') }}</p>
    <p><strong>عدد القطاعات المحجوزة:</strong> {{ $totalSectors }}</p>
    <p><strong>عدد الإدارات:</strong> {{ $totalDepartments }}</p>
    <p><strong>عدد الموظفين:</strong> {{ $totalUsers }}</p>
    <p><strong>الإجمالي:</strong> {{ $totalAmount }}</p>

    <table border="1" cellpadding="5" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>الترتيب</th>
                <th>القطاع</th>
                <th>الادارات الرئيسيه</th>
                <th>الادارات الفرعيه</th>
                <th>عدد الموظفين</th>
                <th>المبلغ</th>
            </tr>
        </thead>
        
        <tbody>
            @foreach ($data as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->sector_name }}</td>
                    <td>{{ $row->main_departments_count }}</td>
                    <td>{{ $row->sub_departments_count }}</td>
                    <td>{{ $row->user_count }}</td>
                    <td>{{ number_format($row->total_amount, 2) }} <span>د. ك</span></td> 
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
