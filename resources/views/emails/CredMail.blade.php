<!DOCTYPE html>
<html>

<head>
    <title>{{ $mailData['title'] }}</title>
</head>

<body>


    <h2>{{ $mailData['body'] }}</h2>
    <p><b>اسم الدخول : </b>{{ $mailData['username'] }}</p><br />
    <p><b> كلمة السر : </b>{{ $mailData['password'] }}</p><br />

</body>

</html>
