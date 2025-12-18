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
            line-height: 1.5; 
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
    
    // LOGIC MULTI-SUPPLIER
    $items = is_string($record->items) ? json_decode($record->items, true) : $record->items;
    $itemsCollection = collect($items);
    
    // Ambil ID supplier unik
    $supplierIds = $itemsCollection->pluck('supplier_id')->unique()->filter();
    // Ambil Nama-nama Supplier
    $supplierNames = \App\Models\Supplier::whereIn('id', $supplierIds)->pluck('name')->join(', ');
    
    // Ambil detail SupplierItem untuk ditampilkan di tabel
    $supplierItemIds = $itemsCollection->pluck('supplier_item_id')->unique()->filter();
    $supplierItems = \App\Models\SupplierItem::with('supplier')->whereIn('id', $supplierItemIds)->get()->keyBy('id');
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
                <strong>Daftar Pemasok:</strong><br>
                {{ $supplierNames ?: 'Tidak ada data pemasok' }}
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
                <th style="width: 25%;">Nama Barang</th>
                <th style="width: 20%;">Pemasok</th> <th style="width: 10%;" class="text-center">Jumlah</th>
                <th style="width: 10%;" class="text-center">Satuan</th>
                <th style="width: 15%;" class="text-center">Harga Satuan</th>
                <th style="width: 20%;" class="text-center">Total Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
            @php
                $dbItem = $supplierItems->get($item['supplier_item_id']);
                $itemName = $dbItem ? $dbItem->nama_item : 'Produk Dihapus';
                
                // Ambil nama supplier per baris
                $rowSupplierName = '-';
                if (!empty($item['supplier_id'])) {
                    $supp = \App\Models\Supplier::find($item['supplier_id']);
                    $rowSupplierName = $supp ? $supp->name : '-';
                } elseif ($dbItem && $dbItem->supplier) {
                    $rowSupplierName = $dbItem->supplier->name;
                }
            @endphp
            <tr>
                <td>{{ $itemName }}</td>
                <td>{{ $rowSupplierName }}</td> <td class="text-center">{{ $item['quantity'] }}</td>
                <td class="text-center">{{ $item['unit'] ?? 'pcs' }}</td>
                <td class="text-right">Rp {{ number_format($item['price'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item['total'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right"><strong>Total Pembelian</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($record->grand_total, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <table class="signature-table">
        <tr>
            <td style="width: 50%;">
                <p>Dibuat Oleh,</p>
                <div class="signature-space"></div>
                <p style="text-decoration: underline;"><strong>( M. N. Aef )</strong></p>
                <p>Adm. Sparepart</p>
            </td>
            <td style="width: 50%;">
                <p>Diketahui Oleh,</p>
                <div class="signature-space">
                    @if(file_exists($ttdPath))
                        <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents($ttdPath)) }}" alt="Tanda Tangan" style="height: 50px; width: auto;">
                    @endif
                </div>
                <p style="text-decoration: underline;"><strong>( Gunawan )</strong></p>
                <p>Mgr. Engineering</p>
            </td>
        </tr>
    </table>
</div>

</body>
</html>