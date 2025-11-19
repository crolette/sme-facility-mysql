<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{__(
                'website_contact.mail.contact.title',
                ['subject' => $request['subject'], 'company' => $request['company'], 'name' => $request['first_name']  . ' ' .  $request['last_name']]
            )}} </title>
    <style>
         body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #e3ebfc;
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
            text-align: center;
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
            margin-block: auto;
        }
        .logo {
            width: 120px;
            height: 120px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://sme-facility.com/images/logo.png" alt="" class="">
            
            <h1>{{__(
                'website_contact.mail.contact.title',
                ['subject' => $request['subject'], 'company' => $request['company'], 'name' => $request['first_name']  . ' ' .  $request['last_name']]
            )}}</h1>
        </div>
        <div class="content">
            {{-- <p>Sent: {{ date('d-m-Y') }}</p> --}}
            <p>{{__('common.name')}}: {{$request['first_name']}} {{$request['last_name']}}</p>
            <p>{{__('common.phone')}}: {{$request['phone_number']}}</p>
            <p>{{__('providers.company_name')}}: {{$request['company']}}</p>
            <p>{{__('providers.vat_number')}}: {{$request['vat_number']}}</p>
            <p>{{__('website_contact.subject')}}: {{ __('website_contact.reason.' . strtolower($request['subject'])) }}</p>
            <p>{{__('common.email')}}: {{$request['email']}}</p>
            <p>{{__('website_contact.message')}}: {{$request['message']}}</p>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>{{ config('app.address', '') }}</p>
        </div>
    </div>
</body>
</html>