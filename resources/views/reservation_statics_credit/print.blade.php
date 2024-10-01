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
        }
        .header-box img {
            width: 100px; /* Adjust image size */
            height: auto;
        }
    </style>
</head>
<body>

    <div class="header-box">
        <h1>احصائيات بدل حجز يوم {{ $dayOfWeek }} من شهر {{ $currentMonth }}</h1>
        <img src="{{ asset('img/logo.png') }}" alt="Logo">
    </div>

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
