<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Barang Masuk (PO)</title>
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
        .header-table td { vertical-align: middle; }
        .header-info { text-align: center; }
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
        $periodeTeks = '';

        switch ($data['filterPeriode']) {
            case 'rentang_tanggal':
                $mulai = isset($data['tanggal_mulai']) ? \Carbon\Carbon::parse($data['tanggal_mulai'])->translatedFormat('d F Y') : 'N/A';
                $akhir = isset($data['tanggal_akhir']) ? \Carbon\Carbon::parse($data['tanggal_akhir'])->translatedFormat('d F Y') : 'N/A';
                $periodeTeks = "{$mulai} s/d {$akhir}";
                break;
            case 'bulanan':
                $tanggal = \Carbon\Carbon::parse($data['tanggal']);
                $periodeTeks = 'Bulan ' . $tanggal->translatedFormat('F Y');
                break;
            case 'tahunan':
                $tanggal = \Carbon\Carbon::parse($data['tanggal']);
                $periodeTeks = 'Tahun ' . $tanggal->translatedFormat('Y');
                break;
            default: // 'harian'
                $tanggal = \Carbon\Carbon::parse($data['tanggal']);
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
            <h2>Laporan Barang Masuk</h2>
            <p>Periode: {{ $periodeTeks }}</p>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 3%; vertical-align: middle;">No</th>
                    <th rowspan="2" style="vertical-align: middle;">Nama Barang</th>
                    <th rowspan="2" style="width: 4%; vertical-align: middle;">Qty</th>
                    <th rowspan="2" style="width: 6%; vertical-align: middle;">Satuan</th>
                    <th colspan="2" style="width: 20%;">Estimasi Harga</th>
                    <th rowspan="2" style="width: 12%; vertical-align: middle;">Keterangan</th>
                    <th rowspan="2" style="width: 7%; vertical-align: middle;">Pembayaran</th>
                    <th rowspan="2" style="vertical-align: middle;">Supplier</th>
                    <th rowspan="2" style="width: 8%; vertical-align: middle;">Tgl. Pesan</th>
                    <th rowspan="2" style="width: 8%; vertical-align: middle;">Tgl. Terima</th>
                </tr>
                <tr>
                    <th>Harga Satuan</th>
                    <th>Total Pesanan</th>
                </tr>
            </thead>
            <tbody>
                @php $rowNumber = 1; @endphp
                @forelse ($records as $record)
                    @php $itemCount = count($record->items); @endphp
                    @if($itemCount > 0)
                        @foreach($record->items as $item)
                            <tr>
                                @if ($loop->first)
                                    <td class="text-center" rowspan="{{ $itemCount }}">{{ $rowNumber++ }}</td>
                                @endif

                                <td>{{ \App\Models\SupplierItem::find($item['supplier_item_id'])->nama_item ?? 'N/A' }}</td>
                                <td class="text-center">{{ $item['quantity'] ?? 0 }}</td>
                                <td class="text-center">{{ $item['unit'] ?? 'pcs' }}</td>
                                <td class="text-right">Rp {{ number_format($item['price'] ?? 0, 0, ',', '.') }}</td>

                                @if ($loop->first)
                                    <td class="text-right" rowspan="{{ $itemCount }}">Rp {{ number_format($record->grand_total, 0, ',', '.') }}</td>
                                    <td rowspan="{{ $itemCount }}">{{ $record->notes }}</td>
                                    <td class="text-center" rowspan="{{ $itemCount }}">{{ ucfirst($record->payment_method) }}</td>
                                    <td rowspan="{{ $itemCount }}">{{ $record->supplier->name ?? 'N/A' }}</td>
                                    <td class="text-center" rowspan="{{ $itemCount }}">{{ $record->created_at->format('d-m-Y') }}</td>
                                    <td class="text-center" rowspan="{{ $itemCount }}">{{ $record->updated_at->format('d-m-Y') }}</td>
                                @endif
                            </tr>
                        @endforeach
                    @endif
                @empty
                    <tr>
                        <td colspan="11" class="text-center">Tidak ada data untuk periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right"><strong>Total Pengeluaran</strong></td>
                    <td class="text-right"><strong>Rp {{ number_format($records->sum('grand_total'), 0, ',', '.') }}</strong></td>
                    <td colspan="5"></td>
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