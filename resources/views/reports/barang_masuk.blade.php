<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Barang Masuk</title>
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
            vertical-align: middle; 
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
            <p>Periode: {{ $periode }}</p>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 3%;">No</th>
                    <th>Nama Barang</th>
                    <th style="width: 4%;">Qty</th>
                    <th style="width: 6%;">Satuan</th>
                    <th style="width: 10%;">Harga Satuan</th>
                    <th style="width: 10%;">Total Pesanan</th>
                    <th style="width: 12%;">Keterangan</th>
                    <th style="width: 7%;">Pembayaran</th>
                    <th>Supplier</th>
                    <th style="width: 8%;">Tgl. Pesan</th>
                    <th style="width: 8%;">Tgl. Terima</th>
                </tr>
            </thead>
            <tbody>
                @php $rowNumber = 1; @endphp
                @forelse ($data as $record)
                    @php $itemCount = count($record->items); @endphp
                    @if($itemCount > 0)
                        @foreach($record->items as $item)
                            <tr>
                                @if ($loop->first)
                                    <td class="text-center" rowspan="{{ $itemCount }}">{{ $rowNumber++ }}</td>
                                @endif

                                <td>{{ $supplierItems->get($item['supplier_item_id'])->nama_item ?? 'N/A' }}</td>
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
                    <td class="text-right"><strong>Rp {{ number_format($data->sum('grand_total'), 0, ',', '.') }}</strong></td>
                    <td colspan="5"></td>
                </tr>
            </tfoot>
        </table>

        <table class="signature-table">
            <tr>
                <td style="width: 50%;">
                    <p>Dibuat Oleh,</p>
                    <div class="signature-space"></div>
                    <p style="text-decoration: underline;"><strong>M. N. Aef</strong></p>
                    <p>Adm. Sparepart</p>
                </td>
                <td style="width: 50%;">
                    <p>Diketahui Oleh,</p>
                    <div class="signature-space"></div>
                    <p style="text-decoration: underline;"><strong>Gunawan</strong></p>
                    <p>Mgr. Engineering</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>