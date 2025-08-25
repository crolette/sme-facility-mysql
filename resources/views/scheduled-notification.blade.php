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
                @case('contract_expiry')
                    <div class="alert">
                        <strong>⚠️ Expiration de contrat</strong>
                    </div>
                    <p>Un contrat arrive à expiration prochainement :</p>
                    <div class="details">
                        <strong>Contrat :</strong> {{ $data['contract_name'] ?? 'N/A' }}<br>
                        <strong>Date d'expiration :</strong> {{ isset($data['expiry_date']) ? \Carbon\Carbon::parse($data['expiry_date'])->format('d/m/Y') : 'N/A' }}<br>
                        @if(isset($data['contract_reference']))
                            <strong>Référence :</strong> {{ $data['contract_reference'] }}<br>
                        @endif
                        @if(isset($data['supplier_name']))
                            <strong>Fournisseur :</strong> {{ $data['supplier_name'] }}<br>
                        @endif
                    </div>
                    <p>Nous vous recommandons de prendre les dispositions nécessaires pour le renouvellement ou la résiliation de ce contrat.</p>
                    @break

                @case('maintenance_due')
                    <div class="alert">
                        <strong>🔧 Maintenance programmée</strong>
                    </div>
                    <p>Une maintenance est programmée prochainement :</p>
                    <div class="details">
                        <strong>Asset :</strong> {{ $data['asset_name'] ?? 'N/A' }}<br>
                        <strong>Date de maintenance :</strong> {{ isset($data['maintenance_date']) ? \Carbon\Carbon::parse($data['maintenance_date'])->format('d/m/Y') : 'N/A' }}<br>
                        @if(isset($data['maintenance_type']))
                            <strong>Type :</strong> {{ $data['maintenance_type'] }}<br>
                        @endif
                        @if(isset($data['location']))
                            <strong>Localisation :</strong> {{ $data['location'] }}<br>
                        @endif
                    </div>
                    <p>Veillez à organiser cette maintenance dans les délais prévus.</p>
                    @break

                @case('warranty_end')
                    <div class="alert">
                        <strong>🛡️ Fin de garantie</strong>
                    </div>
                    <p>La garantie d'un asset arrive à expiration :</p>
                    <div class="details">
                        <strong>Asset :</strong> {{ $data['asset_name'] ?? 'N/A' }}<br>
                        <strong>Fin de garantie :</strong> {{ isset($data['warranty_end_date']) ? \Carbon\Carbon::parse($data['warranty_end_date'])->format('d/m/Y') : 'N/A' }}<br>
                        @if(isset($data['warranty_type']))
                            <strong>Type de garantie :</strong> {{ $data['warranty_type'] }}<br>
                        @endif
                        @if(isset($data['supplier_name']))
                            <strong>Fournisseur :</strong> {{ $data['supplier_name'] }}<br>
                        @endif
                    </div>
                    <p>Après cette date, l'asset ne sera plus couvert par la garantie constructeur.</p>
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