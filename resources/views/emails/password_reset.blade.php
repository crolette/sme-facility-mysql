@extends('emails.layouts.custom')

@section('content')
    <h1 style="color: #1f2937; font-size: 24px; margin-bottom: 20px;">Password Reset</h1>
    <p>Hello {{$user->full_name}},</p>
    <p style="margin-bottom: 20px;">
        Forgot your password? No problem! Click the button below to reset it.
    </p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $url }}" class="button">
            Reset password
        </a>
    </div>

    <p>If you didn't request this, you can safely ignore this email.</p>

    
@endsection

@section('footer')
 <p>If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser: <a href="{{$url}}">{{$url}}</a></p>
@endsection
