<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Notification</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background-color: white; padding: 30px; border-radius: 8px; }
        .header { border-bottom: 2px solid #e9ecef; padding-bottom: 20px; margin-bottom: 30px; }
        .title { color: #333; margin: 0; }
        .content { line-height: 1.6; color: #555; }
        .alert { padding: 15px; margin: 20px 0; border-left: 4px solid #007bff; background-color: #f8f9fa; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; color: #888; font-size: 12px; }
        .button { display: inline-block; padding: 12px 24px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 20px 0; }
        .details { background-color: #f8f9fa; padding: 15px; border-radius: 4px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="title">{{ $recipientName ? 'Bonjour ' . $recipientName : 'Bonjour' }}</h1>
        </div>

        <div class="content">
            @switch($notificationType)
                @case('depreciation_end_date')
                    <div class="alert">
                        <strong>🛡️ Fin d'amortissement</strong>
                    </div>
                    <p>L'amortissement d'un asset arrive à expiration :</p>
                    <div class="details">
                        <strong>Asset :</strong> {{ $data['subject'] ?? 'N/A' }} - {{ $data['reference'] ?? '' }}<br>
                        <strong>Emplacement :</strong> {{ $data['location'] ?? 'N/A' }}<br>
                        <strong>Fin d'amortissement :</strong> {{ isset($data['depreciation_end_date']) ? \Carbon\Carbon::parse($data['depreciation_end_date'])->format('d/m/Y') : 'N/A' }}<br>
                        <strong>Lien :</strong> {{ $data['link'] ?? 'N/A' }}<br>
                    </div>
                    <p>Après cette date, l'asset sera totalement amorti.</p>
                @break

                 @case('notice_date')
                    <div class="alert">
                        <strong>⚠️ Préavis contrat </strong>
                    </div>
                    <p>Un contrat arrive à expiration prochainement :</p>
                    <div class="details">
                        <strong>Contrat :</strong> {{ $data['subject'] ?? 'N/A' }}<br>
                        <strong>Renouvellement :</strong> {{ $data['renewal_type'] ?? 'N/A' }}<br>
                        <strong>Date de préavis :</strong> {{ isset($data['notice_date']) ? \Carbon\Carbon::parse($data['notice_date'])->format('d/m/Y') : 'N/A' }}<br>
                        <strong>Date d'expiration :</strong> {{ isset($data['end_date']) ? \Carbon\Carbon::parse($data['end_date'])->format('d/m/Y') : 'N/A' }}<br>
                        @if(isset($data['provider']))
                            <strong>Fournisseur :</strong> {{ $data['provider'] ?? 'N/A' }}<br>
                        @endif
                        <strong>Lien :</strong> <a href="{{ $data['link'] ?? '' }}">{{ $data['link'] ?? 'N/A' }}</a><br>
                    </div>
                    <p>Nous vous recommandons de prendre les dispositions nécessaires pour le renouvellement ou la résiliation de ce contrat.</p>
                @break

                @case('end_date')
                    <div class="alert">
                        <strong>⚠️ Expiration de contrat</strong>
                    </div>
                    <p>Un contrat arrive à expiration prochainement :</p>
                    <div class="details">
                        <strong>Contrat :</strong> {{ $data['contract_name'] ?? 'N/A' }}<br>
                        <strong>Renouvellement :</strong> {{ $data['renewal_type'] ?? 'N/A' }}<br>
                        <strong>Date d'expiration :</strong> {{ isset($data['end_date']) ? \Carbon\Carbon::parse($data['end_date'])->format('d/m/Y') : 'N/A' }}<br>
                        @if(isset($data['provider']))
                            <strong>Fournisseur :</strong> {{ $data['provider'] ?? 'N/A' }}<br>
                        @endif
                        <strong>Lien :</strong> <a href="{{ $data['link'] ?? '' }}">{{ $data['link'] ?? 'N/A' }}</a><br>
                    </div>
                    <p>Nous vous recommandons de prendre les dispositions nécessaires pour le renouvellement ou la résiliation de ce contrat.</p>
                @break           

                @case('planned_at')
                    <div class="alert">
                        <strong>⚠️ Intervention à prévoir</strong>
                    </div>
                    <p>Une intervention est à prévoir prochainement :</p>
                    <div class="details">
                        <strong>Type :</strong> {{ $data['type'] ?? 'N/A' }}<br>
                        <strong>Priorité :</strong> {{ $data['priority'] ?? 'N/A' }}<br>
                        <strong>Intervention pour </strong> {{ $data['subject'] ?? 'N/A' }}<br>
                        <strong>Description : </strong> {{ $data['description'] ?? 'N/A' }}<br>
                        <strong>Date d'intervention :</strong> {{ isset($data['planned_at']) ? \Carbon\Carbon::parse($data['planned_at'])->format('d/m/Y') : 'N/A' }}<br>
                       
                        <strong>Lien :</strong> <a href="{{ $data['link'] ?? '' }}">{{ $data['link'] ?? 'N/A' }}</a><br>
                    </div>
                    <p>Nous vous recommandons de prendre les dispositions nécessaires pour l'intervention.</p>
                    @break

                @case('next_maintenance_date')
                    <div class="alert">
                        <strong>🔧 Maintenance programmée</strong>
                    </div>
                    <p>Une maintenance est programmée prochainement :</p>
                    <div class="details">
                        <strong>Asset/Lieu :</strong> {{ $data['subject'] ?? 'N/A' }}<br>
                        <strong>Reference :</strong> {{ $data['reference'] ?? 'N/A' }}<br>
                        <strong>Date de maintenance :</strong> {{ isset($data['maintenance_date']) ? \Carbon\Carbon::parse($data['maintenance_date'])->format('d/m/Y') : 'N/A' }}<br>
                        @if(isset($data['location']))
                            <strong>Localisation :</strong> {{ $data['location'] ?? 'N/A' }}<br>
                        @endif
                        <strong>Lien :</strong> <a href="{{ $data['link'] ?? '' }}">{{ $data['link'] ?? 'N/A' }}</a><br>
                    </div>
                    <p>Veillez à organiser cette maintenance dans les délais prévus.</p>
                    @break

                @case('end_warranty_date')
                    <div class="alert">
                        <strong>🛡️ Fin de garantie</strong>
                    </div>
                    <p>La garantie d'un asset/lieu arrive à expiration :</p>
                    <div class="details">
                        <strong>Asset/Lieu :</strong> {{ $data['subject'] ?? 'N/A' }}<br>
                        <strong>Reference :</strong> {{ $data['reference'] ?? 'N/A' }}<br>
                        <strong>Fin de garantie :</strong> {{ isset($data['end_warranty_date']) ? \Carbon\Carbon::parse($data['end_warranty_date'])->format('d/m/Y') : 'N/A' }}<br>
                           @if(isset($data['location']))
                            <strong>Localisation :</strong> {{ $data['location'] ?? 'N/A' }}<br>
                        @endif
                       <strong>Lien :</strong> <a href="{{ $data['link'] ?? '' }}">{{ $data['link'] ?? 'N/A' }}</a><br>
                    </div>
                    <p>Après cette date, l'asset/lieu ne sera plus couvert par la garantie.</p>
                    @break

                

                @default
                    <div class="alert">
                        <strong>📢 Notification</strong>
                    </div>
                    <p>Vous avez une nouvelle notification de type : {{ $notificationType }}</p>
                    @if(!empty($data))
                        <div class="details">
                            @foreach($data as $key => $value)
                                <strong>{{ ucfirst(str_replace('_', ' ', $key)) }} :</strong> {{ $value }}<br>
                            @endforeach
                        </div>
                    @endif
            @endswitch

            @if(isset($data['dashboard_url']))
                <a href="{{ $data['dashboard_url'] }}" class="button">Voir dans le tableau de bord</a>
            @endif
        </div>

        <div class="footer">
            <p>Cette notification a été générée automatiquement par votre système de facility management.</p>
            <p>Si vous avez des questions, contactez votre administrateur.</p>
        </div>
    </div>
</body>
</html>