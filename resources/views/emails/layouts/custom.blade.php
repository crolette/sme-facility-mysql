{{-- resources/views/emails/layouts/custom.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* Your custom email CSS */
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: oklch(42.935% 0.11812 258.322);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .details {
            padding: 24px;
            background-color: oklch(0.9656 0.0171 248.01);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 5px;
        }

        .details + * {
                margin-top: 40px;
         }

        .alert { padding: 15px; margin: 20px 0; border-left: 4px solid #007bff; background-color: #f8f9fa; }

        
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            {{-- Logo --}}
            <div style="text-align: center; margin-bottom: 30px;">
                <img src={{ env('APP_LOGO') }} alt="{{ config('app.name') }}" style="max-width: 160px;">
            </div>

            {{-- Content --}}
            @yield('content')

            {{-- Footer --}}
            <p>
                Regards,<br>
                The SME-Facility Team
            </p>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #6b7280; font-size:12px">
                 @yield('footer')
                
                 <p>Do not hesitate to <a href="mailto:support@sme-facility.com">contact SME-Facility's team</a> if you have any problem.</p>
                 <p>
                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved. </p>
                <a href={{ config('app.url') }}>{{ config('app.url') }}</a>
                <p >FWebxp SRL - {{ env('APP_ADDRESS') }}</p>
            </div>
        </div>
    </div>
</body>
</html>