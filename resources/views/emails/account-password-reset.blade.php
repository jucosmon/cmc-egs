<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Reset</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.5; color: #111827;">
    <p>Hi {{ $user->first_name }},</p>

    <p>Your account password has been reset by an administrator.</p>

    <p>
        Temporary password: <strong>{{ $temporaryPassword }}</strong>
    </p>

    <p>
        Please log in using your official email and this temporary password.
        We recommend changing it right away after logging in.
    </p>

    <p>Thank you,<br>CMC EGS Team</p>
</body>
</html>
