<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Stok Persediaan</title>
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
            <h2>Laporan Stok Persediaan</h2>
            <p>Periode: {{ $periode }}</p>
        </div>

        @php
            
            $grandTotalMasuk = 0;
            $grandTotalKeluar = 0;
            $grandTotalStokAkhir = 0;
            $chunkSize = 0; 

            if (isset($allRecords) && $allRecords->isNotEmpty()) {
                foreach ($allRecords as $record) {
                    $masuk = $record->stockMovements->where('type', 'in')->sum('quantity');
                    $keluar = $record->stockMovements->where('type', 'out')->sum('quantity');
                    
                    $grandTotalMasuk += $masuk;
                    $grandTotalKeluar += $keluar;
                    $grandTotalStokAkhir += $record->stock;
                }
               
                $chunkSize = (isset($data) && $data->first()) ? $data->first()->count() : 0; 
            }
        @endphp

        @if (isset($allRecords) && $allRecords->isNotEmpty() && $chunkSize > 0)
            
            @foreach ($data as $chunk)
                
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th>Nama Barang</th>
                            <th style="width: 10%;">Satuan</th>
                            <th style="width: 15%;">Stok Awal</th>
                            <th style="width: 15%;">Masuk</th>
                            <th style="width: 15%;">Keluar</th>
                            <th style="width: 15%;">Stok Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($chunk as $record)
                            @php
                                $masukHariIni = $record->stockMovements->where('type', 'in')->sum('quantity');
                                $keluarHariIni = $record->stockMovements->where('type', 'out')->sum('quantity');
                                $stokAwal = $record->stock - $masukHariIni + $keluarHariIni;
                            @endphp
                            <tr>
                                <td class="text-center">{{ ($loop->parent->index * $chunkSize) + $loop->iteration }}</td>
                                <td>{{ $record->name }}</td>
                                <td class="text-center">{{ $record->unit }}</td>
                                <td class="text-center">{{ $stokAwal }}</td>
                                <td class="text-center">{{ $masukHariIni }}</td>
                                <td class="text-center">{{ $keluarHariIni }}</td>
                                <td class="text-center">{{ $record->stock }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    
                    @if ($loop->last)
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-right"><strong>Total</strong></td>
                            <td class="text-center"><strong>{{ $grandTotalMasuk }}</strong></td>
                            <td class="text-center"><strong>{{ $grandTotalKeluar }}</strong></td>
                            <td class="text-center"><strong>{{ $grandTotalStokAkhir }}</strong></td> 
                        </tr>
                    </tfoot>
                    @endif
                    
                </table>

                {{-- Jika ini BUKAN chunk terakhir, paksa pindah halaman (page break) --}}
                @if (!$loop->last)
                    <div style="page-break-after: always;"></div>
                @endif

            @endforeach 

        @else
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th>Nama Barang</th>
                        <th style="width: 10%;">Satuan</th>
                        <th style="width: 15%;">Stok Awal</th>
                        <th style="width: 15%;">Masuk</th>
                        <th style="width: 15%;">Keluar</th>
                        <th style="width: 15%;">Stok Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data untuk tanggal ini.</td>
                    </tr>
                </tbody>
            </table>
        @endif

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