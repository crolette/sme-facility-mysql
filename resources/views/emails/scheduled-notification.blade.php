@extends('emails.layouts.custom')

@section('content')
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
                        <br>
                        <div style="display:flex; align-items:center; justify-content:center; margin-top:8px">
                            <a href="{{  $data['link'] }}" class="button">Voir dans le tableau de bord</a>
                        </div>
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
                        <div style="display:flex; align-items:center; justify-content:center; margin-top:8px">
                            <a href="{{  $data['link'] }}" class="button">Voir dans le tableau de bord</a>
                        </div>
                    </div>
                    <p>Nous vous recommandons de prendre les dispositions nécessaires pour le renouvellement ou la résiliation de ce contrat.</p>
                @break

                @case('end_date')
                    <div class="alert">
                        <strong>⚠️ Expiration de contrat</strong>
                    </div>
                    <p>Un contrat arrive à expiration prochainement :</p>
                    <div class="details">
                        <strong>Contrat :</strong> {{ $data['subject'] ?? 'N/A' }}<br>
                        <strong>Renouvellement :</strong> {{ $data['renewal_type'] ?? 'N/A' }}<br>
                        <strong>Date d'expiration :</strong> {{ isset($data['end_date']) ? \Carbon\Carbon::parse($data['end_date'])->format('d/m/Y') : 'N/A' }}<br>
                        @if(isset($data['provider']))
                            <strong>Fournisseur :</strong> {{ $data['provider'] ?? 'N/A' }}<br>
                        @endif
                        <div style="display:flex; align-items:center; justify-content:center; margin-top:8px">
                            <a href="{{  $data['link'] }}" class="button">Voir dans le tableau de bord</a>
                        </div>
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
                        <strong>Intervention pour : </strong> {{ $data['subject'] ?? 'N/A' }}<br>
                        <strong>Description : </strong> {{ $data['description'] ?? 'N/A' }}<br>
                        <strong>Date d'intervention :</strong> {{ isset($data['planned_at']) ? \Carbon\Carbon::parse($data['planned_at'])->format('d/m/Y') : 'N/A' }}<br>
                       
                         <div style="display:flex; align-items:center; justify-content:center; margin-top:8px">
                            <a href="{{  $data['link'] }}" class="button">Voir dans le tableau de bord</a>
                        </div>
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
                        <strong>Date de maintenance :</strong> {{ isset($data['next_maintenance_date']) ? \Carbon\Carbon::parse($data['next_maintenance_date'])->format('d/m/Y') : 'N/A' }}<br>
                        @if(isset($data['location']))
                            <strong>Localisation :</strong> {{ $data['location'] ?? 'N/A' }}<br>
                        @endif
                        <div style="display:flex; align-items:center; justify-content:center; margin-top:8px">
                            <a href="{{  $data['link'] }}" class="button">Voir dans le tableau de bord</a>
                        </div>
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
                       <div style="display:flex; align-items:center; justify-content:center; margin-top:8px">
                            <a href="{{  $data['link'] }}" class="button">Voir dans le tableau de bord</a>
                        </div>
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

           

@endsection

@section('footer')
    <p>Cette notification a été générée automatiquement par votre système de facility management. Si vous avez des questions, contactez votre administrateur.</p>
@endsection