<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ config('app.name') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 0 0 5px 5px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #f1b22e;
            color: #000;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PASSWORD RESET BLLABLA {{ config('app.name') }}!</h1>
        </div>
        
        <div class="content">
            <p>Hi {{ $user->name }},</p>
            
            <p>Thank you for joining {{ config('app.name') }}! We're excited to have you as part of our community.</p>
            
            <p>Here's what you can do next:</p>
            <ul>
                <li>Complete your profile</li>
                <li>Explore our features</li>
                <li>Connect with other members</li>
            </ul>
            
            <p>To get started, click the button below:</p>
            
            <a href="{{ url('/dashboard') }}" class="button">Go to Dashboard</a>
            
            <p>If you have any questions, feel free to reply to this email - we're always here to help!</p>
            
            <p>Best regards,<br>
            The {{ config('app.name') }} Team</p>
        </div>
        
        <div class="footer">
            <p>This email was sent to {{ $user->email }}. If you didn't create this account, please ignore this email.</p>
            <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>{{ config('app.address', '') }}</p>
        </div>
    </div>
</body>
</html>