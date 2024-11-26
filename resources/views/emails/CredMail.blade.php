<!DOCTYPE html>
<html>

<head>
    <title>{{ $mailData['title'] }}</title>
</head>

<body>


    <h2>{{ $mailData['body'] }}</h2>
    <p><b>اسم الدخول : </b>{{ $mailData['username'] }}</p><br />
    @if (isset($mailData['password']) && $mailData['password'])
        <p><b> كلمة السر : </b>{{ $mailData['password'] }}</p><br />
    @elseif(isset($mailData['code']) && $mailData['code'])
        <p><b> كود التفعيل: </b>{{ $mailData['code'] }}</p><br />
    @endif
</body>

</html>
