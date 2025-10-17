<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice Pembelian - {{ $record->po_number }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 9px;
            color: #333;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .header-info {
            text-align: center;
        }
        .header-info h1 { margin: 0; font-size: 20px; }
        .header-info p { margin: 2px 0; font-size: 11px; }

        .report-title h2 {
            text-align: center;
            font-size: 16px;
            margin: 20px 0 5px 0;
            text-transform: uppercase;
        }
        .report-title p {
            text-align: center;
            font-size: 12px;
            margin: 0 0 20px 0;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .items-table th, .items-table td {
            padding: 5px;
            border: 1px solid #ccc;
            text-align: left;
            vertical-align: middle;
            word-wrap: break-word;
        }
        .items-table thead th {
            background-color: #4A5568;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        .items-table tfoot td {
            font-weight: bold;
            background-color: #f2f2f2;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .info-section {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-section td {
            line-height: 1.5; /* Menambah jarak antar baris */
        }

        .signature-table {
            width: 100%;
            margin-top: 40px;
            text-align: center;
            page-break-inside: avoid;
        }
        .signature-table p { margin: 0; line-height: 1.5; }
        .signature-space { height: 60px; }
    </style>
</head>
<body>
@php
    $logoPath = public_path('images/Logo_MAS.png'); 
    $ttdPath  = public_path('images/ttd.jpg'); 
@endphp

<div class="container">
    <table class="header-table">
        <tr>
            <td style="width: 20%;">
                @if(file_exists($logoPath))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" style="max-width: 100px;">
                @endif
            </td>
            <td class="header-info" style="width: 60%;">
                <h1>PT MAKMUR ARTHA SEJAHTERA</h1>
                <p>Jl. Ki Ageng Tapa Blok Nambo No.168, Astapada, Kec. Tengah Tani, Kabupaten Cirebon, Jawa Barat 45153</p>
                <p>Telp. (0231) 245206, (0231) 245207</p>
            </td>
            <td style="width: 20%;"></td>
        </tr>
    </table>
    
    <div class="report-title">
        <h2>Laporan Penerimaan Barang Sparepart</h2>
    </div>

    <table class="info-section">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <strong>Informasi Pemasok:</strong><br>
                {{ $record->supplier->name ?? '-' }}<br>
                {{ $record->supplier->address ?? 'Alamat tidak tersedia' }}<br>
                {{ $record->supplier->phone_number ?? 'Telepon tidak tersedia' }}
            </td>
            <td style="width: 40%; text-align: right; vertical-align: top;">
                <strong>No. PO:</strong> {{ $record->po_number }}<br>
                <strong>Tanggal Pemesanan:</strong> {{ $record->created_at->translatedFormat('d F Y') }}<br>
                @if($record->status === 'completed')
                    <strong>Tanggal Penerimaan:</strong> {{ $record->updated_at->translatedFormat('d F Y') }}
                @endif
            </td>
            </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Nama Barang</th>
                <th style="width: 10%;" class="text-center">Jumlah</th>
                <th style="width: 10%;" class="text-center">Satuan</th>
                <th style="width: 20%;" class="text-center">Harga Satuan</th>
                <th style="width: 20%;" class="text-center">Total Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($record->items as $item)
            <tr>
                <td>{{ \App\Models\SupplierItem::find($item['supplier_item_id'])->nama_item ?? 'Produk Dihapus' }}</td>
                <td class="text-center">{{ $item['quantity'] }}</td>
                <td class="text-center">{{ $item['unit'] ?? 'pcs' }}</td>
                <td class="text-right">Rp {{ number_format($item['price'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item['total'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right"><strong>Total Pembelian</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($record->grand_total, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <table class="signature-table">
        <tr>
            <td style="width: 50%;">
                <p>Dibuat Oleh,</p>
                <div class="signature-space"></div>
                <p style="text-decoration: underline;"><strong>( Nama Admin Sparepart )</strong></p>
                <p>Adm. Sparepart</p>
            </td>
            <td style="width: 50%;">
                <p>Diketahui Oleh,</p>
                <div class="signature-space">
                    @if(file_exists($ttdPath))
                        <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents($ttdPath)) }}" alt="Tanda Tangan" style="height: 50px; width: auto;">
                    @endif
                </div>
                <p style="text-decoration: underline;"><strong>( Nama Manajer )</strong></p>
                <p>Mgr. Engineering</p>
            </td>
        </tr>
    </table>
</div>

</body>
</html>