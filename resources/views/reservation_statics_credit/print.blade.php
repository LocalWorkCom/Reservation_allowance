<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة</title>
    <style>
        body {
            direction: rtl;
            font-family: 'Almarai', sans-serif;
        }
        h1, p {
            text-align: center;
        }
        .header-box {
            background-color: #e0e0e0; /* Gray background */
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-box h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
            flex: 1;
            text-align: left; 
        }
        .header-box img {
            width: 100px; 
            height: auto;
            margin-right: 20px; 
        }
        table {
            width: 80%; 
            margin: 0 auto; 
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        td {
            font-size: 18px;
        }
    </style>
</head>
<body>

    <div class="header-box">
        <img src="{{ asset('img/logo.png') }}" alt="Logo"> 
        <h1>احصائيات بدل حجز يوم {{ $dayOfWeek }} من شهر {{ $currentMonth }}</h1>
    </div>

    <table>
        <tr>
            <th>العنوان</th>
            <th>القيمة</th>
        </tr>
        <tr>
            <td>التاريخ</td>
            <td>{{ $date }}</td>
        </tr>
        <tr>
            <td>حجز جزئي العدد</td>
            <td>{{ $partial_reservation_count }}</td>
        </tr>
        <tr>
            <td>حجز جزئي المبلغ</td>
            <td>{{ $partial_reservation_amount }}</td>
        </tr>
        <tr>
            <td>حجز كلي العدد</td>
            <td>{{ $full_reservation_count }}</td>
        </tr>
        <tr>
            <td>حجز كلي المبلغ</td>
            <td>{{ $full_reservation_amount }}</td>
        </tr>
        <tr>
            <td>اجمالي المبلغ</td>
            <td>{{ $total_amount }}</td>
        </tr>
    </table>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
