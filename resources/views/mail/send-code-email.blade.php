<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Verification Code') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 30px;
            border: 1px solid #dee2e6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 10px;
        }
        .verification-code {
            background-color: #0d6efd;
            color: white;
            font-size: 32px;
            font-weight: bold;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin: 30px 0;
            letter-spacing: 4px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
            color: #6c757d;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">{{ config('app.name', 'Laravel') }}</div>
            <h1>{{ __('Verification Code') }}</h1>
        </div>

        <p>{{ __('Hello') }},</p>
        
        <p>{{ __('We have received a request to verify your account. Please use the following verification code:') }}</p>

        <div class="verification-code">
            {{ $code }}
        </div>

        <div class="warning">
            <strong>⚠️ {{ __('Important') }}:</strong> {{ __('This code will expire in 15 minutes. Do not share this code with anyone.') }}
        </div>

        <p>{{ __('If you did not request this code, you can safely ignore this email.') }}</p>

        <div class="footer">
            <p>{{ __('Thanks') }},<br>{{ config('app.name') }}</p>
            <p><small>{{ __('This is an automated email, please do not reply to this message.') }}</small></p>
        </div>
    </div>
</body>
</html>

