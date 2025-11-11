<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Barang Keluar</title>
    <style>
        /* --- CSS --- */
        body { font-family: 'Helvetica', sans-serif; font-size: 8px;  color: #333; }
        .container { width: 100%; margin: 0 auto; padding: 10px; }
        .header-table { width: 100%; border-bottom: 2px solid #333; padding-bottom: 8px; margin-bottom: 8px; }
        .header-table td { vertical-align: middle; }
        .header-info { text-align: center; }
        .header-info h1 { margin: 0; font-size: 18px; } 
        .header-info p { margin: 1px 0; font-size: 10px; }
        .report-title h2 { text-align: center; font-size: 14px; margin: 15px 0 3px 0; text-transform: uppercase; }
        .report-title p { text-align: center; font-size: 11px; margin: 0 0 15px 0; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 10px;  }
        .items-table th, .items-table td { padding: 4px; border: 1px solid #ccc; text-align: left; vertical-align: top; word-wrap: break-word; }
        .items-table thead th { background-color: #4A5568; color: white; font-weight: bold; text-align: center; vertical-align: middle; font-size: 8px;  }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .signature-table { width: 100%; margin-top: 30px; text-align: center; page-break-inside: avoid; }
        .signature-table p { margin: 0; line-height: 1.4; }
        .signature-space { height: 50px; }
        .badge { padding: 2px 5px; border-radius: 4px; color: white; font-size: 8px; text-transform: capitalize; display: inline-block; white-space: nowrap;  }
        .completed { background-color: #28a745; } 
        .pending { background-color: #ffc107; color: black } 
        .items-list { list-style: none; padding-left: 0; margin: 0; }
        .items-list li { margin-bottom: 2px; }
        .correction-info { font-size: 7.5px;  color: #555; }
        .correction-info p { margin: 0 0 2px 0; line-height: 1.2;}
    </style>
</head>
<body>
    @php
         $logoPath = public_path('images/Logo_MAS.png');
         $periodeTeks = $periode ?? 'N/A'; // Ambil dari variabel $periode yang dikirim Controller
         $statusFilter = $filters['status_filter_keluar'] ?? 'all';
         $statusTeks = match($statusFilter) {'completed' => 'Completed', 'pending' => 'Pending', default => 'Semua'};

         $koreksiFilter = $filters['status_koreksi_keluar'] ?? 'all';
         $koreksiTeks = match($koreksiFilter) {'ya' => 'Pernah Dikoreksi', 'tidak' => 'Belum Dikoreksi', default => 'Semua'};
         
         $records = $data ?? collect();
    @endphp

    <div class="container">
        <table class="header-table">
             <tr>
                 <td style="width: 20%;">
                     @if(file_exists($logoPath))
                          <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" style="max-width: 80px;"> {{-- Kecilkan logo --}}
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
            <h2>Laporan Barang Keluar</h2>
            <p>Periode: {{ $periodeTeks }} (Status: {{ $statusTeks }}, Koreksi: {{ $koreksiTeks }})</p>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 3%;">No</th>
                    <th style="width: 12%;">Nama Pengambil</th> 
                    <th style="width: 8%;">Bagian</th>
                    <th style="width: 5%;">Shift</th>
                    <th style="width: 15%;">Keterangan</th> 
                    <th style="width: 20%;">Daftar Barang</th> 
                    <th style="width: 8%;">Tgl Dibuat</th>
                    <th style="width: 9%;">Waktu Keluar</th>
                    {{-- 👇 Tambah Header Kolom Koreksi 👇 --}}
                    <th style="width: 13%;">Info Koreksi</th>
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
                            <ul class="items-list">
                                {{-- ... loop @foreach ($items as $item) ... --}}
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
                                         $limitedName = \Illuminate\Support\Str::limit($productName, 30, '...'); // Sedikit perpendek
                                    @endphp
                                    <li>• {{ $limitedName }} ({{ $quantity }} {{ $unit }})</li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="text-center">{{ $record->created_at->format('d-m-Y') }}</td>
                        <td class="text-center">{{ $record->status === 'completed' ? $record->updated_at->format('d-m-Y H:i') : '-' }}</td>
                        <td class="correction-info">
                            @if ($record->corrections && $record->corrections->isNotEmpty())
                                <ul class="items-list" style="padding-left: 10px; margin: 0;">
                                    @foreach ($record->corrections as $correction)
                                        @php
                                            $time = $correction->created_at->format('d/m H:i');
                                            $reason = $correction->reason;
                                            $productName = $correction->product?->name ?? $correction->product_name_cache ?? 'Barang N/A';
                                            $oldQty = $correction->quantity_before ?? null;
                                            $newQty = $correction->quantity_after ?? null;
                                            $diff = $correction->difference ?? 0;
                                            $diffText = $diff > 0 ? "+{$diff}" : $diff;
                                        @endphp
                                        <li style="margin-bottom: 5px; border-bottom: 1px dashed #ccc; padding-bottom: 4px;">
                                            <p style="margin: 0; font-weight: bold;">{{ \Illuminate\Support\Str::limit($productName, 25, '...') }}</p>
                                            <p style="margin: 0;">Sebelum: {{ $oldQty ?? 'N/A' }}</p>
                                            <p style="margin: 0;">Sesudah: {{ $newQty ?? 'N/A' }}</p>
                                            <p style="margin: 0;">Selisih: {{ $diffText }}</p>
                                            <p style="margin: 0;">({{ $reason }})</p>
                                            <p style="margin: 0; font-size: 7px;">[{{ $time }}]</p>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p style="text-align: center; font-style: italic; color: #777;">Tidak ada koreksi</p>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data untuk periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <table class="signature-table">
             <tr>
                 <td style="width: 50%;">
                     <p>Dibuat Oleh,</p>
                     <div class="signature-space"></div>
                     <p style="text-decoration: underline;"><strong>M. N. Aef</strong></p> 
                     <p>Adm. Gudang Sparepart</p> 
                 </td>
                 <td style="width: 50%;">
                     <p>Mengetahui,</p>
                     <div class="signature-space"></div>
                     <p style="text-decoration: underline;"><strong>Gunawan</strong></p> 
                     <p>Mng. Engineering</p> 
                 </td>
             </tr>
        </table>
    </div>
</body>
</html>