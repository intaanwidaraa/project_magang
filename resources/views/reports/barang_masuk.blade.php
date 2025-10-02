<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Barang Masuk (PO)</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 11px;
            color: #333;
            background-color: #fff;
            margin: 0;
        }
        .container {
            width: 100%;
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
            padding: 8px;
            border: 1px solid #ccc;
            text-align: left;
            vertical-align: top;
        }
        .items-table thead th {
            background-color: #4A5568;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        .items-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .items-table tfoot td {
            font-weight: bold;
            background-color: #f2f2f2;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .signature-table {
            width: 100%;
            margin-top: 40px;
            text-align: center;
            page-break-inside: avoid;
        }
        .signature-table p { margin: 0; line-height: 1.5; }
        .signature-space { height: 60px; }
        ul { margin: 0; padding-left: 15px; list-style-type: none; }
        li { margin-bottom: 3px; }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/Logo_MAS.png');
        $tanggal = \Carbon\Carbon::parse($data['tanggal']);
        $periodeTeks = '';
        switch ($data['filterPeriode']) {
            case 'bulanan':
                $periodeTeks = 'Bulan ' . $tanggal->translatedFormat('F Y');
                break;
            case 'tahunan':
                $periodeTeks = 'Tahun ' . $tanggal->translatedFormat('Y');
                break;
            default: // harian
                $periodeTeks = 'Tanggal ' . $tanggal->translatedFormat('d F Y');
                break;
        }
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
            <h2>Laporan Barang Masuk (PO)</h2>
            <p>Periode: {{ $periodeTeks }}</p>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 10%;">Tanggal</th>
                    <th>Pemasok</th>
                    <th>Jenis Produk</th>
                    <th class="text-center">Jumlah</th>
                    <th class="text-right">Harga Satuan</th>
                    <th class="text-right">Total Harga</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($records as $record)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $record->created_at->format('d-m-Y') }}</td>
                        <td>{{ $record->supplier->name }}</td>
                        <td>
                            <ul>
                                @foreach($record->items as $item)
                                    <li>{{ \App\Models\SupplierItem::find($item['supplier_item_id'])->nama_item ?? 'N/A' }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="text-center">
                            <ul>
                                @foreach($record->items as $item)
                                    <li>{{ $item['quantity'] ?? 0 }} {{ $item['unit'] ?? 'pcs' }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="text-right">
                            <ul>
                                @foreach($record->items as $item)
                                    <li>Rp {{ number_format($item['price'] ?? 0, 0, ',', '.') }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="text-right">Rp {{ number_format($record->grand_total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data untuk periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="text-right"><strong>Total Pengeluaran</strong></td>
                    <td class="text-right"><strong>Rp {{ number_format($records->sum('grand_total'), 0, ',', '.') }}</strong></td>
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
                    <div class="signature-space"></div>
                    <p style="text-decoration: underline;"><strong>( Nama Manajer )</strong></p>
                    <p>Mgr. Engineering</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>