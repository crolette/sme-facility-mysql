<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Intervention added by provider</title>
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
            <h1 class="title">{{  'Intervention added' }}</h1>
 
        </div>

        <div class="content">
                <div class="alert">
                        <strong>🛡️ Intervention added</strong>
                    </div>
                    <div class="details">
                        <strong>Description :</strong> {{ $interventionAction->description }} <br>
                        <strong>type :</strong> {{ $interventionAction->type }}<br>
                        <strong>Email :</strong> {{ $interventionAction->creator_email }}<br>
                        <strong>Costs :</strong> {{ $interventionAction->intervention_costs ?? '0' }}<br>
                        <strong>Started at :</strong> {{ $interventionAction->started_at }}<br>
                        <strong>Finished at :</strong> {{ $interventionAction->finished_at }}<br>
                         {{-- <a href={{ $interventionAction->interventionable->locationRoute}} class="button">View problem</a> --}}
                    </div>
        </div>

        <div class="footer">
            <p>Cette notification a été générée automatiquement par votre système de facility management.</p>
            <p>Si vous avez des questions, contactez votre administrateur.</p>
        </div>
    </div>
</body>
</html>