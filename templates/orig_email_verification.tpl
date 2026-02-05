<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code</title>
</head>
<body style="margin:0;padding:0;font-family:Arial,sans-serif;background-color:#f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f4f4f4;padding:20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color:#ffffff;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding:40px 30px;text-align:center;background-color:#4CAF50;border-radius:8px 8px 0 0;">
                            <h1 style="margin:0;color:#ffffff;font-size:24px;font-weight:bold;">Two-Factor Authentication</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:40px 30px;">
                            <p style="margin:0 0 20px;color:#333333;font-size:16px;line-height:1.5;">Hello {$user->username},</p>
                            <p style="margin:0 0 30px;color:#333333;font-size:16px;line-height:1.5;">Your verification code is:</p>
                            <div style="background-color:#f8f9fa;border:2px dashed #4CAF50;border-radius:8px;padding:20px;text-align:center;margin:0 0 30px;">
                                <span style="font-size:32px;font-weight:bold;color:#4CAF50;letter-spacing:8px;">{$code}</span>
                            </div>
                            <p style="margin:0 0 20px;color:#666666;font-size:14px;line-height:1.5;">This code will expire in <strong>10 minutes</strong>.</p>
                            <p style="margin:0;color:#999999;font-size:12px;line-height:1.5;">If you didn't request this code, please ignore this email.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 30px;text-align:center;background-color:#f8f9fa;border-radius:0 0 8px 8px;">
                            <p style="margin:0;color:#999999;font-size:12px;">This is an automated message, please do not reply.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
