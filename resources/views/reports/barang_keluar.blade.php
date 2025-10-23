<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Barang Keluar</title>
    <style>
        
        body { font-family: 'Helvetica', sans-serif; font-size: 9px; color: #333; }
        .container { max-width: 900px; margin: 0 auto; padding: 20px; }
        .header-table { width: 100%; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 10px; }
        .header-table td { vertical-align: middle; }
        .header-info { text-align: center; }
        .header-info h1 { margin: 0; font-size: 20px; }
        .header-info p { margin: 2px 0; font-size: 11px; }
        .report-title h2 { text-align: center; font-size: 16px; margin: 20px 0 5px 0; text-transform: uppercase; }
        .report-title p { text-align: center; font-size: 12px; margin: 0 0 20px 0; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .items-table th, .items-table td { padding: 5px; border: 1px solid #ccc; text-align: left; vertical-align: top; /* Ubah ke top */ word-wrap: break-word; }
        .items-table thead th { background-color: #4A5568; color: white; font-weight: bold; text-align: center; vertical-align: middle; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .signature-table { width: 100%; margin-top: 40px; text-align: center; page-break-inside: avoid; }
        .signature-table p { margin: 0; line-height: 1.5; }
        .signature-space { height: 60px; }
        .badge { padding: 2px 6px; border-radius: 4px; color: white; font-size: 9px; text-transform: capitalize; display: inline-block; }
        .completed { background-color: #28a745; } /* Hijau */
        .pending { background-color: #ffc107; color: black } /* Kuning */
        .items-list { list-style: none; padding-left: 0; margin: 0; }
        .items-list li { margin-bottom: 3px; }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/Logo_MAS.png');
        $periodeTeks = '';
        $periodeFilter = $data['filterPeriodeKeluar'] ?? 'harian'; // Gunakan filterPeriodeKeluar
        $tanggal = isset($data['tanggal']) ? \Carbon\Carbon::parse($data['tanggal']) : now();

        switch ($periodeFilter) {
            case 'rentang_tanggal':
                $mulai = isset($data['tanggal_mulai']) ? \Carbon\Carbon::parse($data['tanggal_mulai'])->translatedFormat('d F Y') : 'N/A';
                $akhir = isset($data['tanggal_akhir']) ? \Carbon\Carbon::parse($data['tanggal_akhir'])->translatedFormat('d F Y') : 'N/A';
                $periodeTeks = "{$mulai} s/d {$akhir}";
                break;
            case 'bulanan':
                $periodeTeks = 'Bulan ' . $tanggal->translatedFormat('F Y');
                break;
            case 'tahunan':
                $periodeTeks = 'Tahun ' . $tanggal->translatedFormat('Y');
                break;
            default: // 'harian'
                $periodeTeks = 'Tanggal ' . $tanggal->translatedFormat('d F Y');
                break;
        }
         $statusFilter = $data['status_filter_keluar'] ?? 'all';
         $statusTeks = match($statusFilter) {'completed' => 'Completed', 'pending' => 'Pending', default => 'Semua'};
    @endphp

    <div class="container">
        {{-- Header Kop Surat --}}
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

        {{-- Judul Laporan --}}
        <div class="report-title">
            <h2>Laporan Barang Keluar</h2>
            <p>Periode: {{ $periodeTeks }} (Status: {{ $statusTeks }})</p>
        </div>

        {{-- Tabel Data Barang Keluar --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 3%;">No</th>
                    <th style="width: 15%;">Nama Pengambil</th>
                    <th style="width: 10%;">Bagian</th>
                    <th style="width: 5%;">Shift</th>
                    <th>Keterangan</th>
                    <th style="width: 25%;">Daftar Barang</th>
                    <th style="width: 8%;">Status</th>
                    <th style="width: 8%;">Tgl Dibuat</th>
                    <th style="width: 10%;">Waktu Keluar</th>
                </tr>
            </thead>
            <tbody>
                @php $rowNumber = 1; @endphp
                @forelse ($records as $record)
                    <tr>
                        <td class="text-center">{{ $rowNumber++ }}</td>
                        <td>{{ $record->requester_name }}</td>
                        <td>{{ $record->department }}</td>
                        <td class="text-center">{{ $record->shift ? 'Shift ' . $record->shift : '-' }}</td>
                        <td>{{ $record->notes }}</td>
                        <td>
                            {{-- Logika untuk menampilkan list barang --}}
                            <ul class="items-list">
                                 @php
                                    $items = $record->items;
                                    if (is_string($items)) $items = json_decode($items, true);
                                    else $items = json_decode(json_encode($items), true);
                                    $items = collect($items);

                                    $productIdsNeedingFetch = $items->whereNull('product_name')->pluck('product_id')->unique()->filter();
                                    $products = $productIdsNeedingFetch->isNotEmpty() ? \App\Models\Product::whereIn('id', $productIdsNeedingFetch)->get()->keyBy('id') : collect();
                                @endphp
                                @foreach ($items as $item)
                                    @php
                                        $quantity = $item['quantity'] ?? 0;
                                        $productName = $item['product_name'] ?? 'Barang Dihapus';
                                        $unit = $item['product_unit'] ?? 'pcs';
                                        if (($productName === 'Barang Dihapus' || is_null($productName)) && isset($item['product_id'])) {
                                            $product = $products->get($item['product_id']);
                                            if ($product) {
                                                $productName = $product->name;
                                                $unit = $product->unit ?? 'pcs';
                                            }
                                        }
                                        $limitedName = \Illuminate\Support\Str::limit($productName, 40, '...');
                                    @endphp
                                    <li>• {{ $limitedName }} ({{ $quantity }} {{ $unit }})</li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="text-center"><span class="badge {{ $record->status }}">{{ $record->status }}</span></td>
                        <td class="text-center">{{ $record->created_at->format('d-m-Y') }}</td>
                        <td class="text-center">{{ $record->status === 'completed' ? $record->updated_at->format('d-m-Y H:i') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data untuk periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
            {{-- Hapus tfoot karena tidak ada total --}}
        </table>

        {{-- Tanda Tangan --}}
        <table class="signature-table">
            <tr>
                <td style="width: 50%;">
                    <p>Dibuat Oleh,</p>
                    <div class="signature-space"></div>
                    <p style="text-decoration: underline;"><strong>M. N. Aef</strong></p>