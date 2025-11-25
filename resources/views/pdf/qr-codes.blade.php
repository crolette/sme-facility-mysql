<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>SME-Facility - QR-Codes</title>
    <style>
        /* Votre CSS Avery L7160 ici */
        @page {
            size: A4;
            margin: 15mm 7mm 15mm 7mm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 10px;
        }

        .labels-page {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .labels-page td {
            width: 45.7mm;
            height: 25.4mm;
            padding: 1.25mm;
            vertical-align: top;
            border: none;
        }

        .label-table {
            width: 100%;
            height: 22.9mm; /* 38.1mm - 2.5mm padding */
            border-collapse: collapse;
            table-layout: fixed;
            /* border: 1px solid #ddd; Pour visualisation */
        }

        .label-horizontal .qr-cell {
            width: 28mm;
            padding: 1mm;
            vertical-align: middle;
            text-align: center;
        }

        .label-horizontal .text-cell {
            width: 28mm;
            padding: 1mm;
            vertical-align: middle;
        }

        .qr-code img {
            width: 18mm;
            height: 20mm;
        }

        .title { font-weight: bold; font-size: 8px; margin-bottom: 1mm; }
        .description { font-size: 8px; color: #333; margin-bottom: 1mm; }
        .code {  font-size: 8px; }

        .labels-page, .labels-page tr, .labels-page td {
    page-break-inside: avoid !important;
}

/* Retirez la classe page-break et utilisez ceci à la place */
.new-page {
    page-break-before: always;
}
    </style>
</head>
<body>
   @foreach ($codes->chunk(40) as $pageIndex => $pageItems)
  @if ($pageIndex > 0)
    <div class="new-page"></div>
@endif
    
    <table class="labels-page">
        @foreach ($pageItems->chunk(4) as $rowItems)
            <tr>
                @foreach ($rowItems as $code)
                    <td>
                        <table class="label-table label-horizontal">
                            <tr>
                                <td class="qr-cell">
                                    <div class="qr-code">
                                        <img src="{{ $code->getQRCodeForPdf }}" alt="QR" className="w-2 h-2">
                                    </div>
                                </td>
                                <td class="text-cell">
                                    <div class="text-content">
                                        <div class="title">{{ $code->name }}</div>
                                        <div class="code">{{ $code->reference_code }}</div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                @endforeach
                
                {{-- Compléter la ligne si moins de 3 éléments --}}
                @for ($i = $rowItems->count(); $i < 3; $i++)
                    <td><table class="label-table"><tr><td></td></tr></table></td>
                @endfor
            </tr>
        @endforeach
    </table>
@endforeach
</body>
</html>