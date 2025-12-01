@extends('emails.layouts.custom')

@section('content')
            @switch($notificationType)
                @case('depreciation_end_date')
                    <div class="alert">
                        <strong>🛡️ {{__('notifications.depreciation_end_date')}}</strong>
                    </div>
                    <p></p>
                    <div class="details">
                        <strong>{{trans_choice('assets.title', 1)}} : </strong> {{ $data['subject'] }}<br>
                        <strong>{{trans_choice('locations.location', 1)}} :</strong> {{$data['location'] }}<br>
                        <strong>{{__('assets.depreciation_end_date')}} :</strong> {{ isset($data['depreciation_end_date']) ? \Carbon\Carbon::parse($data['depreciation_end_date'])->format('d/m/Y') : 'N/A' }}<br>
                        <br>
                        <div style="display:flex; align-items:center; justify-content:center; margin-top:8px">
                            <a href="{{  $data['link'] }}" class="button">{{__('actions.see-type', ['type' => trans_choice('assets.title', 1)])}}</a>
                        </div>
                    </div>
                    <p>{{__('notifications.depreciation_end_date_description')}}</p>
                @break

                 @case('notice_date')
                    <div class="alert">
                        <strong>⚠️ {{__('notifications.notice_date')}} </strong>
                    </div>
                    <p>{{__('notifications.notice_date_subtitle')}} :</p>
                    <div class="details">
                        <strong>{{__('common.name')}} :</strong> {{ $data['provider'] }}<br>
                        {{-- <strong>{{__('common.type')}} :</strong> {{ $notification->notifiable->type->value }}<br> --}}
                        {{-- <strong>{{__('contracts.internal_ref')}} :</strong> {{ $notification->notifiable->internal_reference ?? 'N/A' }}<br> --}}
                        <strong>{{__('contracts.renewal_type')}} :</strong> {{__(`contracts.renewal_type.` . $data['renewal_type'] )}}<br>
                        <strong>{{__('contracts.notice_date')}} :</strong> {{ isset($data['notice_date']) ? \Carbon\Carbon::parse($data['notice_date'])->format('d/m/Y') : 'N/A' }}<br>
                        <strong>{{__('contracts.end_date')}} :</strong> {{ isset($data['end_date']) ? \Carbon\Carbon::parse($data['end_date'])->format('d/m/Y') : 'N/A' }}<br>
                        @if(isset($data['provider']))
                            <strong>{{trans_choice('providers.title', 1)}} :</strong> {{ $data['provider'] }}<br>
                        @endif
                        <div style="display:flex; align-items:center; justify-content:center; margin-top:8px">
                             <a href="{{  $data['link'] }}" class="button">{{__('actions.see-type', ['type' => trans_choice('contracts.title', 1)])}}</a>
                            
                        </div>
                    </div>
                    <p>{{__('notifications.notice_date_description')}}</p>
                @break

                @case('end_date')
                    <div class="alert">
                        <strong>⚠️ {{__('notifications.end_date')}}</strong>
                    </div>
                    <p>{{__('notifications.end_date_subtitle')}}</p>
                    <div class="details">
                        <strong>{{__('common.name')}} :</strong> {{ $data['subject'] }}<br>
                        {{-- <strong>{{__('common.type')}} :</strong> {{ __(`contracts.type.` . $notification->notifiable->type->value) }}<br>
                        <strong>{{__('contracts.internal_ref')}} :</strong> {{ $notification->notifiable->internal_reference }}<br> --}}
                        <strong>{{__('contracts.renewal_type')}} :</strong> {{__(`contracts.renewal_type.` . $data['renewal_type'])}}<br>
                        @if ($notification->notifiable->notice_date)
                        <strong>{{__('contracts.notice_date')}} :</strong> {{\Carbon\Carbon::parse($data['end_date'])->format('d/m/Y') }}<br>
                            
                        @endif
                        @if(isset($data['provider']))
                            <strong>{{trans_choice('providers.title', 1)}} :</strong> {{ $data['provider'] }}<br>
                        @endif
                        <div style="display:flex; align-items:center; justify-content:center; margin-top:8px">
                            <a href="{{  $data['link'] }}" class="button">{{__('actions.see-type', ['type' => trans_choice('contracts.title', 1)])}}</a>
                        </div>
                    </div>
                    <p>{{__('notifications.end_date_description')}}</p>
                @break           

                @case('planned_at')
                    <div class="alert">
                        <strong>⚠️ {{__('notifications.planned_at')}}</strong>
                    </div>
                    <p>{{__('notifications.planned_at_subtitle')}}</p>
                    <div class="details">
                        <strong>{{__('common.type')}} :</strong> {{ $data['type'] ?? 'N/A' }}<br>
                        <strong>{{__('interventions.priority.title')}} :</strong> {{ $data['priority'] ?? 'N/A' }}<br>
                        <strong>{{trans_choice('locations.location', 1)}} : </strong> {{ $data['subject'] ?? 'N/A' }}<br>
                        <strong>{{__('common.description')}} : </strong> {{ $data['description'] ?? 'N/A' }}<br>
                        <strong>{{__('notifications.planned_at')}} :</strong> {{ isset($data['planned_at']) ? \Carbon\Carbon::parse($data['planned_at'])->format('d/m/Y') : 'N/A' }}<br>
                       
                         <div style="display:flex; align-items:center; justify-content:center; margin-top:8px">
                            <a href="{{  $data['link'] }}" class="button">{{__('actions.see-type', ['type' => trans_choice('interventions.title', 1)])}}</a>
                        </div>
                    </div>
                    <p> {{__('notifications.planned_at_description')}}</p>
                    @break

                @case('next_maintenance_date')
                    <div class="alert">
                        <strong>🔧 {{__('notifications.next_maintenance_date')}}</strong>
                    </div>
                    <p>{{__('notifications.next_maintenance_date_subtitle')}} :</p>
                    <div class="details">
                        <strong>{{trans_choice('locations.location', 1)}} :</strong> {{ $data['subject'] ?? 'N/A' }}<br>
                        <strong>Reference :</strong> {{ $data['reference'] ?? 'N/A' }}<br>
                        <strong>{{__('notifications.next_maintenance_date')}} :</strong> {{ isset($data['next_maintenance_date']) ? \Carbon\Carbon::parse($data['next_maintenance_date'])->format('d/m/Y') : 'N/A' }}<br>
                        @if(isset($data['location']))
                            <strong>{{trans_choice('locations.location', 1)}} :</strong> {{ $data['location'] ?? 'N/A' }}<br>
                        @endif
                        <div style="display:flex; align-items:center; justify-content:center; margin-top:8px">
                            <a href="{{  $data['link'] }}" class="button">{{__('actions.see-type', ['type' => trans_choice('interventions.title', 1)])}}</a>
                        </div>
                    </div>
                    <p>{{__('notifications.next_maintenance_date_description')}}</p>
                    @break

                @case('end_warranty_date')
                    <div class="alert">
                        <strong>🛡️ {{__('notifications.end_warranty_date')}}</strong>
                    </div>
                    <p> {{__('notifications.end_warranty_date_subtitle')}} :</p>
                    <div class="details">
                        <strong>{{trans_choice('locations.location', 1)}} :</strong> {{ $data['subject'] ?? 'N/A' }}<br>
                        <strong>{{__('common.reference_code')}} :</strong> {{ $data['reference'] ?? 'N/A' }}<br>
                        <strong>{{__('assets.warranty_end_date')}} :</strong> {{ isset($data['end_warranty_date']) ? \Carbon\Carbon::parse($data['end_warranty_date'])->format('d/m/Y') : 'N/A' }}<br>
                           @if(isset($data['location']))
                            <strong>{{trans_choice('locations.location', 1)}} :</strong> {{ $data['location'] ?? 'N/A' }}<br>
                        @endif
                       <div style="display:flex; align-items:center; justify-content:center; margin-top:8px">
                            <a href="{{  $data['link'] }}" class="button">{{__('actions.see-type', ['type' => trans_choice('assets.title', 1)])}}</a>
                        </div>
                    </div>
                    <p>{{__('notifications.end_warranty_date_description')}}</p>
                    @break

                

                @default
                    <div class="alert">
                        <strong>📢 {{trans_choice('notifications.title', 1)}}</strong>
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
<p>{{__('notifications.disclaimer')}}</p>
@endsection