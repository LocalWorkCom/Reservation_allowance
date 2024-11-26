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
<div class="row" >
        <div class="container welcome col-11">
            <div class="d-flex justify-content-between">
    <p style="text-align: center;"> الموظفين للإدارة الفرعية: {{ $subDepartment->name }}</p>
    
    
    </div>
           

           </div>
       </div>
   
      
       <div class="container col-11 mt-3 py-5  " >
       <div class="d-flex justify-content-between pb-3"dir="rtl">
       <h4> الفترة من: <span class="text-info"> {{ $startDate->format('Y-m-d') }}</span> إلى: <span class="text-info">{{ $endDate->format('Y-m-d') }}</span></h4>
           
            </div>
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
