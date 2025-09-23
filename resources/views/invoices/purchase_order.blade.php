<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice Pembelian - {{ $record->po_number }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
            color: #333;
            background-color: #fff;
            margin: 0;
        }
        .invoice-box {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
        }
        table {
            width: 100%;
            line-height: inherit;
            text-align: left;
            border-collapse: collapse;
        }
        .top-table td {
            padding: 5px;
            vertical-align: middle;
        }
        .header-info {
            text-align: center;
        }
        .header-info h1 { margin: 0; font-size: 20px; word-break: keep-all; white-space: nowrap; }
        .header-info p { margin: 2px 0; font-size: 11px; }
        .line { border-top: 2px solid #333; margin: 15px 0; }
        .text-right { text-align: right; }

        /* Gaya Tabel Item */
        .items-table {
            margin-top: 20px;
        }
        .items-table th, .items-table td {
            padding: 10px 12px;
            border: 1px solid #e0e0e0;
            text-align: left !important; /* [Perubahan] Memaksa semua rata kiri */
        }
        .items-table thead th {
            background-color: #3f51b5;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
        }
        .items-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .total-section {
            margin-top: 20px;
            text-align: right;
        }
        .total-section strong {
            font-size: 16px;
            font-weight: bold;
        }
        .signature-table {
            width: 100%;
            margin-top: 50px;
            text-align: center;
            page-break-inside: avoid;
        }
        .signature-table p {
            margin: 0;
            line-height: 1.5;
        }
        .signature-space {
            height: 60px;
        }
    </style>
</head>
<body>
@php
    $logoPath = public_path('images/Logo_MAS.png'); 
    $ttdPath  = public_path('images/ttd.jpg'); 
@endphp

<div class="invoice-box">
    <table class="top-table">
        <tr>
            <td style="width: 20%;"> @if(file_exists($logoPath))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" style="width: 100%; max-width: 120px;">
                @endif
            </td>
            <td class="header-info" style="width: 60%;"> <h1>PT MAKMUR ARTHA SEJAHTERA</h1>
                <p>Jl. Ki Ageng Tapa Blok Nambo No.168, Astapada, Kec. Tengah Tani, Kabupaten Cirebon, Jawa Barat 45153</p>
                <p>Telp. (0231) 245206, (0231) 245207</p>
            </td>
            <td style="width: 20%;"></td> </tr>
    </table>
    <div class="line"></div>
    
    <h2 style="text-align: center; color: #3f51b5; margin-bottom: 25px;">LAPORAN PENERIMAAN BARANG SPAREPART</h2>
    <table>
        <tr>
            <td style="width: 60%;">
                <strong>Informasi Pemasok:</strong><br>
                {{ $record->supplier->name ?? '-' }}<br>
                {{ $record->supplier->address ?? 'Alamat tidak tersedia' }}<br>
                {{ $record->supplier->phone_number ?? 'Telepon tidak tersedia' }}
            </td>
            <td class="text-right" style="vertical-align: top;">
                <strong>No. PO:</strong> {{ $record->po_number }}<br>
                <strong>Tanggal:</strong> {{ $record->created_at->translatedFormat('d F Y') }}
            </td>
        </tr>
    </table>

    <p><strong>Detail Pembelian:</strong></p>
    <table class="items-table">
        <thead>
            <tr>
                <th>Nama Produk</th>
                <th>Jumlah</th> <th>Satuan</th>
                <th>Harga Satuan</th> <th>Total Harga</th> </tr>
        </thead>
        <tbody>
            @foreach ($record->items as $item)
            <tr>
                <td>{{ \App\Models\SupplierItem::find($item['supplier_item_id'])->nama_item ?? 'Produk Dihapus' }}</td>
                <td>{{ $item['quantity'] }}</td> <td>{{ $item['unit'] ?? 'pcs' }}</td>
                <td>Rp {{ number_format($item['price'] ?? 0, 0, ',', '.') }}</td> <td>Rp {{ number_format($item['total'] ?? 0, 0, ',', '.') }}</td> </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <strong>Total Pembelian: Rp {{ number_format($record->grand_total, 0, ',', '.') }}</strong>
    </div>

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