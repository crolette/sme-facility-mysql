<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('interventions.assigned') . ' ' . $intervention->interventionable->name . ' ' . $tenant }}</title>
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
            <h1 class="title">{{ __('interventions.assigned_email_title', ['tenant' => $tenant, 'item' => $intervention->interventionable->name]) }}</h1>
        </div>

        <div class="content">
                <div class="alert">
                        <strong>🔧 {{ trans_choice('interventions.title', 1) }}</strong>
                    </div>
                    <div class="details">
                        <strong>{{__('common.description')}} : </strong> {{ $intervention->description }}<br>
                        <strong>{{__('interventions.priority.title')}} : </strong>{{__(`interventions.priority.` . $intervention->priority->value)}}<br>
                        <strong>{{__('common.status')}} : </strong>{{__(`interventions.status.` . $intervention->status->value)}}<br>
                        <strong>{{__('common.name')}} : </strong> {{ $intervention->interventionable->name }}<br>
                        @if($intervention->planned_at)
                            <strong>{{__('interventions.planned_at')}} :</strong> {{ $intervention->planned_at }}<br>
                        @endif
                        
                          <a href={{ $url }} class="button">{{__('actions.see-type', ['type' => trans_choice('interventions.title', 1)])}}</a>
                    </div>
                    <strong>{{__('interventions.assigned_duration')}} </strong>
        </div>

        <div class="footer">
           <p>{{__('notifications.disclaimer')}}</p>
        </div>
    </div>
</body>
</html>