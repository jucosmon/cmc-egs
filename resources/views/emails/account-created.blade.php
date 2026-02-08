<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Welcome to CMC</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f9fafb; color: #111827;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f9fafb; padding: 24px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb;">
                    <tr>
                        <td style="padding: 24px 32px;">
                            <h1 style="margin: 0 0 12px; font-size: 22px;">Welcome to Carmen Municipal College</h1>
                            <p style="margin: 0 0 16px; font-size: 14px; color: #374151;">
                                Hello {{ $user->first_name }},
                            </p>
                            <p style="margin: 0 0 16px; font-size: 14px; color: #374151;">
                                Your {{ str_replace('_', ' ', $user->role) }} account has been created in the CMC Enrollment and Grading System.
                                Below are your login details.
                            </p>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f3f4f6; border-radius: 6px; padding: 16px;">
                                <tr>
                                    <td style="font-size: 14px; padding: 4px 0; color: #111827;">
                                        <strong>Official Email:</strong> {{ $user->email }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-size: 14px; padding: 4px 0; color: #111827;">
                                        <strong>Temporary Password:</strong> {{ $temporaryPassword }}
                                    </td>
                                </tr>
                            </table>
                            <p style="margin: 16px 0 0; font-size: 13px; color: #6b7280;">
                                Please log in and change your password immediately.
                            </p>
                            <p style="margin: 8px 0 0; font-size: 13px; color: #6b7280;">
                                Login URL: {{ config('app.url') }}
                            </p>
                            <p style="margin: 24px 0 0; font-size: 13px; color: #6b7280;">
                                If you have questions, please contact the registrar or IT support.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
