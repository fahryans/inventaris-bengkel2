<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Times New Roman', serif; font-size: 12px; margin: 40px; }
        .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { font-size: 16px; margin: 5px 0; text-transform: uppercase; }
        .header h2 { font-size: 14px; margin: 5px 0; font-weight: normal; }
        .header p { font-size: 11px; margin: 2px 0; }
        .title { text-align: center; font-size: 14px; font-weight: bold; text-decoration: underline; margin: 20px 0; }
        .period { text-align: center; font-size: 12px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; font-size: 11px; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .signature { margin-top: 40px; text-align: right; }
        .signature p { margin: 2px 0; }
        .footer { margin-top: 30px; font-size: 10px; text-align: center; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name', 'SIMA Bengkel') }}</h1>
        <h2>Sistem Inventaris Alat Bengkel</h2>
        <p>Jl. Teknologi No. 123, Gedung A, Lantai 2</p>
        <p>Telp: (021) 1234-5678 | Email: info@sima-bengkel.test</p>
    </div>

    <div class="title">{{ $title }}</div>
    <div class="period">Periode: {{ $date }}</div>

    @if(isset($headers) && isset($rows))
    <table>
        <thead>
            <tr>
                @foreach($headers as $header)
                <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
            <tr>
                @foreach($row as $cell)
                <td>{{ $cell }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
        @yield('content')
    @endif

    <div class="signature">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
        <br><br><br>
        <p>_________________________</p>
        <p><strong>Kepala Laboratorium</strong></p>
    </div>

    <div class="footer">
        <p>Dokumen ini dihasilkan secara otomatis oleh Sistem Inventaris Alat Bengkel</p>
    </div>
</body>
</html>
