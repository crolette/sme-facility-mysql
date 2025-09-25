@extends('emails.layouts.custom')

@section('content')
            <h1 class="title">{{  'Welcome on SME-Facility !'}}</h1>

        <div class="content">
            <p>Thank you for your registration on SME-Facility. Your account and your entreprise account has been created.</p>
            <p><strong>You will receive an e-mail to create your password so you will be able to log in to your account and start using SME-Facility. Please check also your spam.</strong></p>
            <p>Please find below the details of your account.</p>
                    <div class="details">
                        <h2>Admin</h2>
                        <strong>Email :</strong> {{ $user->email }}<br>
                        <strong>Nom :</strong> {{ $user->full_name }}<br>
                    </div>
                    <div class="details">
                        <h2>Company details</h2>
                        <strong>Company name :</strong> {{ $tenant->company_name}}<br>
                        <strong>Address :</strong> {{ $tenant->full_company_address}}<br>
                        <strong>VAT Number :</strong> {{ $tenant->vat_number}}<br>
                        <p><strong>Dashboard :</strong> The url to access your dashboard is the following one and can't be changed : {{ 'https://' . $tenant->domain->domain . '.sme-facility.com'}}</p><br>
                        
                         
                    </div>
                   <a href={{ 'https://' . $tenant->domain->domain . '.sme-facility.com'}} class="button">Go to my dashboard</a>
        </div>
        @endsection

@section('footer')
 <p>This email was sent to {{ $user->email }}. If you didn't create this account, please contact us as soon as possible.</p>
@endsection
