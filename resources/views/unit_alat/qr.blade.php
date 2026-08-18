<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>QR Code - {{ $unitAlat->kode_inventaris }}</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; background: #f5f5f5; }
        .qr-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; max-width: 300px; }
        .qr-code { margin: 20px 0; }
        .info { margin-top: 15px; }
        .info h2 { font-size: 18px; margin: 5px 0; color: #333; }
        .info p { font-size: 14px; color: #666; margin: 3px 0; }
        .print-btn { margin-top: 20px; padding: 10px 20px; background: #4e73df; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; }
        .print-btn:hover { background: #2e59d9; }
        @media print { .print-btn { display: none; } body { background: white; } }
    </style>
</head>
<body>
    <div class="qr-card">
        <div class="qr-code">
            {!! QrCode::size(200)->generate(route('unit-alat.show', $unitAlat)) !!}
        </div>
        <div class="info">
            <h2>{{ $unitAlat->kode_inventaris }}</h2>
            <p>{{ $unitAlat->alat->nama_alat }}</p>
            <p>Lab: {{ $unitAlat->alat->laboratorium->nama_labor }}</p>
            <p>Status: {{ ucfirst($unitAlat->status) }}</p>
        </div>
        <button class="print-btn" onclick="window.print()">
            <i class="fas fa-print"></i> Cetak QR Code
        </button>
    </div>
</body>
</html>