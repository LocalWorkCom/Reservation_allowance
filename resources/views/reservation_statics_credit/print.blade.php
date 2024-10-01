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
        h3 {
            text-align: center;
        }
    </style>
</head>
<body>
<h1>احصائيات بدل حجز يوم {{ $dayOfWeek }} من شهر {{ $currentMonth }}</h1>
    <p>التاريخ: {{ $date }}</p>
    <p>حجز جزئي العدد: {{ $partial_reservation_count }}</p>
    <p>حجز جزئي المبلغ: {{ $partial_reservation_amount }}</p>
    <p>حجز كلي العدد: {{ $full_reservation_count }}</p>
    <p>حجز كلي المبلغ: {{ $full_reservation_amount }}</p>
    <p>اجمالي المبلغ: {{ $total_amount }}</p>
    

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
