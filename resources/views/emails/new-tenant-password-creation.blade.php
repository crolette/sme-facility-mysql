@extends('emails.layouts.custom')

@section('content')
    <h1 style="color: #1f2937; font-size: 24px; margin-bottom: 20px;">Create your password</h1>
    <p>Hello {{$user->full_name}},</p>
    <p style="margin-bottom: 20px;">
        Your account has been created and you need to create a password in order to login to your dashboard. 
    </p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $url }}" class="button">
            Create password
        </a>
    </div>

    
@endsection

@section('footer')
 <p>If you're having trouble clicking the "Create Password" button, copy and paste the URL below into your web browser: {{$url}}</p>
@endsection
