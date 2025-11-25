<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>SME-Facility - Forbidden</title>
</head>
<body>
    <div class="flex items-center justify-center flex-col w-screen h-screen">
               
                <div class="flex flex-col items-center gap-2 bg-slate-50 bg-opacity-75 p-10">
                       <img src={{ env('APP_LOGO') }} alt="{{ config('app.name') }}" style="max-width: 160px;">
                    <h1>FORBIDDEN</h1>
                    <p>
                        No acess
                    </p>
                </div>
            </div>
</body>
</html>