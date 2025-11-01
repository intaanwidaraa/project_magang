<!DOCTYPE html>
<html>
<head>
    <title>FPPB - {{ $record->po_number }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 10mm 8mm 10mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 8pt;
            margin: 0;
            padding: 0;
            color: #000;
        }
        .container {
            padding: 0;
            width: 100%;
        }

        /* --- CSS BARU UNTUK HEADER (REVISI) --- */
        .header-table-revised {
            width: 100%;
            /* border-bottom: 1.5px solid #000; */ /* Garis bawah utama DIHAPUS */
            padding-bottom: 8px; /* Jarak dari garis ke bawah */
            margin-bottom: 15px; /* Jarak ke judul */
            border-collapse: collapse;
            table-layout: fixed; /* Penting untuk lebar kolom */
        }
        .header-table-revised td {
            vertical-align: top; /* Semua rata atas */
            padding: 0;
            font-size: 8pt; /* Ukuran font dasar untuk header */
            line-height: 1.3; /* Jarak antar baris teks */
        }
        .header-logo-col {
            width: 15%; /* Lebar kolom logo */
            /* vertical-align: middle; */ /* Coba top saja */
        }
        .header-company-col {
            width: 48%; /* Lebar kolom info perusahaan - Dikecilkan sedikit */
            padding-left: 10px; /* Jarak dari logo */
        }
        .header-doc-col {
            width: 37%; /* Lebar kolom info dokumen - Dilebarkan sedikit */
            text-align: right; /* Diubah menjadi rata kanan */
            /* padding-left: 20px; */ /* Dihapus */
            vertical-align: top; /* Pastikan rata atas juga */
        }
        .header-logo-col img {
            display: block;
            height: 50px; /* Diubah menjadi 50px */
            width: auto;
        }
        .header-company-col .company-name {
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 2px;
        }
        /* Styling untuk tabel info dokumen (jika menggunakan tabel) */
        .header-doc-col table {
            width: 100%; /* Lebar tabel 100% dari kolom td */
            border-collapse: collapse;
            margin: 0;
            padding: 0;
            vertical-align: top; /* Tabel rata atas */
        }
        .header-doc-col table td {
             vertical-align: top; /* Sel rata atas */
             padding-bottom: 1px; /* Jarak antar baris */
             font-size: 8pt; /* Ukuran font info doc */
             line-height: 1.2; /* Jarak baris info doc */
        }
        /* --- AKHIR CSS HEADER REVISI --- */

        /* Title */
        h2 {
            text-align: center;
            margin: 8px 0 12px 0;
            font-size: 12pt;
            /* text-decoration: underline; */ /* Garis bawah DIHAPUS */
            font-weight: bold;
        }

        /* Item Table */
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0; /* Remove bottom margin */
            table-layout: fixed; /* Fixed layout helps with width consistency */
            font-size: 7pt; /* Smaller font for table content */
        }
        .item-table th, .item-table td {
            border: 1px solid #000;
            padding: 1.5px; /* Reduced padding */
            text-align: center;
            vertical-align: middle; /* Center content vertically */
            height: 15px; /* Fixed height for rows */
            line-height: 1.1; /* Adjust line height for smaller font */
            word-wrap: break-word; /* Allow text wrapping */
        }
        .item-table thead th {
            background-color: #E0E0E0;
            font-weight: bold;
            white-space: normal; /* Allow header text to wrap */
        }
         .item-table tbody td {
             height: 15px; /* Match header height if possible */
             background-color: #fff; /* Ensure white background */
         }
        .item-table .text-left { text-align: left; padding-left: 3px; }
        .item-table .text-right { text-align: right; padding-right: 3px; }

        /* Column Widths (Adjusted) */
        .item-table .col-no { width: 2.5%; }
        .item-table .col-coa { width: 6%; }
        .item-table .col-sku { width: 7%; }
        .item-table .col-name { width: 15%; }
        .item-table .col-qty { width: 3%; }
        .item-table .col-sat { width: 4%; }
        .item-table .col-stock-gudang { width: 4%; }
        .item-table .col-price { width: 7%; }
        .item-table .col-total { width: 7%; }
        .item-table .col-keterangan { width: 9%; }
        .item-table .col-last-buy { width: 6%; }
        .item-table .col-stock-check { width: 3%; }
        .item-table .col-non-check { width: 3%; }
        .item-table .col-po-cash { width: 5%; }
        .item-table .col-supplier { width: 9%; }
        .item-table .col-nopo-tgl { width: 5%; }
        .item-table .col-urgent { width: 5%; }
        /* Total: 100.5% (ok) */


        /* Footer Total */
         .item-table tfoot td {
             border: 1px solid #000;
             padding: 2px;
             font-weight: bold;
             height: 15px; /* Match row height */
             background-color: #E0E0E0;
         }
         .item-table tfoot .total-label {
             text-align: right;
             padding-right: 10px;
         }
          .item-table tfoot .rp-label {
              text-align: left;
              padding-left: 3px;
              border-right: none !important; /* Remove right border */
          }
           .item-table tfoot .total-amount {
               text-align: right;
               padding-right: 3px;
               border-left: none !important; /* Remove left border */
           }

        /* Signature Table */
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px; /* Reduced margin */
        }
        /* --- CSS BARU UNTUK SIGN TABLE --- */
        .sign-table th, .sign-table td { /* Terapkan ke header dan body */
            border: 1px solid #000;
            padding: 2px 3px; /* Sedikit padding */
            text-align: center;
            vertical-align: middle; /* Tengah secara vertikal */
            font-size: 7pt; /* Ukuran font lebih kecil */
            line-height: 1.1;
        }
        .sign-table thead th { /* Gaya khusus untuk header */
            font-weight: bold;
            vertical-align: middle; /* Pastikan header tengah */
        }
        .sign-table tbody td { /* Gaya untuk baris nama dan jabatan */
            height: 40px; /* Tinggi baris nama/jabatan */
            vertical-align: bottom; /* Teks rata bawah */
            padding-bottom: 2px;
        }
        .sign-table tbody tr:last-child td { /* Baris terakhir (Jabatan) */
            height: auto; /* Tinggi otomatis */
            vertical-align: top; /* Teks rata atas */
            padding-bottom: 2px;
            padding-top: 1px;
            font-size: 6.5pt; /* Font jabatan lebih kecil */
        }

        /* Lebar kolom baru (total ~100%) */
        .sign-table .col-sign-diajukan { width: 14%; }
        .sign-table .col-sign-diperiksa { width: 14%; }
        .sign-table .col-sign-diketahui-1 { width: 14%; } /* Sub-kolom Diketahui */
        .sign-table .col-sign-diketahui-2 { width: 14%; } /* Sub-kolom Diketahui */
        .sign-table .col-sign-diterima { width: 14%; }
        .sign-table .col-sign-budget { width: 30%; } /* Kolom budget lebih lebar */
        /* --- AKHIR CSS BARU SIGN TABLE --- */

    </style>
</head>
<body>

<div class="container">
    {{-- HEADER BARU (REVISI DENGAN LOGO YANG BENAR) --}}
    <table class="header-table-revised">
        <tr>
            {{-- Kolom 1: Logo --}}
            <td class="header-logo-col">
                @php
                    // Ganti nama file logo di sini
                    $logoPath = public_path('images/Logo_MAS.png');
                @endphp
                @if(file_exists($logoPath))
                    {{-- Gunakan $logoPath --}}
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" alt="Logo MAS">
                @else
                    @endif
            </td>

            {{-- Kolom 2: Info Perusahaan --}}
            <td class="header-company-col">
                <p class="company-name">PT. MAKMUR ARTHA SEJAHTERA</p>
                <p>JL. KI Ageng Tapa Blok Nambo No. 168 Astapada</p>
                <p>Tengah Tani, Cirebon</p>
            </td>

            {{-- Kolom 3: Info Dokumen (Menggunakan tabel untuk alignment) --}}
            <td class="header-doc-col">
                 <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                     <tr>
                         <td style="text-align: left; width: auto; padding-right: 5px;">Tanggal</td>
                         <td style="text-align: left; width: 5px;">:</td>
                         <td style="text-align: left; font-weight: bold;">{{ $record->created_at->format('d / m / Y') }}</td>
                     </tr>
                     <tr>
                         <td style="text-align: left; padding-right: 5px;">Nomor</td>
                         <td style="text-align: left;">:</td>
                         <td style="text-align: left; font-weight: bold;">{{ $record->po_number }}</td>
                     </tr>
                     <tr>
                         <td style="text-align: left; padding-right: 5px;">Dept / Cost Centre</td>
                         <td style="text-align: left;">:</td>
                         <td style="text-align: left; font-weight: bold;">{{ $record->requester_info ?? 'N/A' }}</td>
                     </tr>
                 </table>
            </td>
        </tr>
    </table>
    {{-- AKHIR HEADER BARU (REVISI DENGAN LOGO YANG BENAR) --}}

    {{-- Judul Form (Tetap sama) --}}
    <h2>Form Permintaan Pembelian Barang</h2>

    {{-- TABEL RINCIAN BARANG --}}
    <table class="item-table">
        <thead>
            {{-- Baris 1: Header Utama --}}
            <tr>
                <th class="col-no" rowspan="2">NO</th>
                <th class="col-coa" rowspan="2">COA</th>
                <th class="col-sku" rowspan="2">Kode Barang</th>
                <th class="col-name" rowspan="2">Nama Barang</th>
                <th class="col-qty" rowspan="2">Qty</th>
                <th class="col-sat" rowspan="2">Sat</th>
                <th class="col-stock-gudang" rowspan="2">Stock di Gudang</th>
                <th colspan="2">Estimasi Harga</th>
                <th class="col-keterangan" rowspan="2">Keterangan</th>
                <th class="col-last-buy" rowspan="2">Terakhir Beli / Kedatangan</th>
                {{-- PERUBAHAN HEADER STOCK/NON --}}
                <th colspan="2">(&#10003;)</th> {{-- Menggunakan Numeric HTML entity --}}
                <th class="col-po-cash" rowspan="2">PO / CASH</th>
                <th class="col-supplier" rowspan="2">SUPPLIER</th>
                <th class="col-nopo-tgl" rowspan="2">NO PO / TGL PO</th>
                <th class="col-urgent" rowspan="2">URGENT</th>
            </tr>
            {{-- Baris 2: Sub-header --}}
            <tr>
                <th class="col-price">Harga</th>
                <th class="col-total">Total</th>
                {{-- PERUBAHAN SUB-HEADER STOCK/NON --}}
                <th class="col-stock-check">Stock</th> {{-- Header Bawah: Stock --}}
                <th class="col-non-check">Non</th>   {{-- Header Bawah: Non --}}
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotal = 0;
                $itemsCollection = collect($record->items);
                $rowCount = $itemsCollection->count();
                $minRows = 15; // Target 15 baris

                // Eager load
                $supplierItemIds = $itemsCollection->pluck('supplier_item_id')->unique()->filter();
                $supplierItems = \App\Models\SupplierItem::with('product')
                                    ->whereIn('id', $supplierItemIds)
                                    ->get()
                                    ->keyBy('id');
            @endphp
            @foreach ($itemsCollection as $index => $item)
                @php
                    $supplierItem = $supplierItems->get($item['supplier_item_id']);
                    $product = optional($supplierItem)->product;
                    $total = ($item['quantity'] ?? 0) * ($item['price'] ?? 0);
                    $grandTotal += $total;
                    $paymentMethod = strtoupper($record->payment_method ?? '');
                    //$lastArrival = 'N/A'; // Dihapus karena diambil dari $lastArrivalDates
                    $supplierName = $record->supplier->name ?? 'N/A';
                    // Ambil status stok dari record utama
                    $stockStatus = $record->stock_status ?? '';
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="col-coa text-left">
                        @php
                            $coaDisplay = 'N/A'; // Default value
                            if (isset($record->coa_name) && !empty($record->coa_name)) {
                                if (preg_match('/\\((.*?)\\)/', $record->coa_name, $matches)) {
                                    $coaDisplay = $matches[1];
                                } else {
                                    $coaDisplay = 'N/A'; // Atau tampilkan nama jika format tdk sesuai: $record->coa_name;
                                }
                            }
                        @endphp
                        {{ $coaDisplay }}
                    </td>
                    <td class="col-sku">{{ optional($product)->sku ?? 'N/A' }}</td>
                    <td class="col-name text-left">{{ optional($supplierItem)->nama_item ?? 'Produk Tdk Ditemukan' }}</td>
                    <td class="col-qty">{{ $item['quantity'] ?? 0 }}</td>
                    <td class="col-sat">{{ $item['unit'] ?? 'pcs' }}</td>
                    <td class="col-stock-gudang">{{ optional($product)->stock ?? 0 }}</td>
                    <td class="col-price text-right">{{ number_format($item['price'] ?? 0, 0, ',', '.') }}</td>
                    <td class="col-total text-right">{{ number_format($total, 0, ',', '.') }}</td>
                    <td class="col-keterangan text-left">{{ $record->notes ?? '-' }}</td>
                    {{-- PERBAIKAN TANGGAL KEDATANGAN --}}
                    <td class="col-last-buy">
                        {{-- Ambil tanggal dari array $lastArrivalDates yang dikirim dari Resource --}}
                        {{ $lastArrivalDates[optional($product)->id] ?? 'N/A' }}
                    </td>
                    {{-- PERBAIKAN BODY STOCK/NON --}}
                    <td class="col-stock-check">{{ ($stockStatus === 'Stock') ? '√' : '' }}</td>
                    <td class="col-non-check">{{ ($stockStatus === 'Non Stock') ? '√' : '' }}</td>
                    <td class="col-po-cash">{{ ($paymentMethod === 'PO' || $paymentMethod === 'CASH') ? $paymentMethod : '' }}</td>
                    <td class="col-supplier text-left">{{ $supplierName }}</td>
                    <td class="col-nopo-tgl">--</td>
                    <td class="col-urgent">{{ ($paymentMethod === 'URGENT') ? '√' : '' }}</td>
                </tr>
            @endforeach

            {{-- Baris Kosong untuk mengisi sisa --}}
            @for ($i = $rowCount; $i < $minRows; $i++)
                <tr>
                    {{-- Pastikan jumlah <td> adalah 17 --}}
                    <td>{{ $i + 1 }}</td> <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td>
                    <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td>
                    <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td>
                    <td>&nbsp;</td> <td>&nbsp;</td> {{-- Tambah satu &nbsp; --}}
                </tr>
            @endfor
        </tbody>
        <tfoot>
             {{-- Baris Total --}}
             <tr>
                 <td colspan="7" class="total-label">Total</td> {{-- Colspan tetap 7 --}}
                 <td class="rp-label">Rp</td>
                 <td class="total-amount">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                 {{-- PERUBAHAN COLSPAN FOOTER --}}
                 <td colspan="8"></td> {{-- Span 8 kolom sisa (Ket+Last+Stock+Non+PO+Sup+NoPO+Urg) --}}
             </tr>
        </tfoot>
    </table>

    {{-- TABEL TANDA TANGAN BARU --}}
    <table class="sign-table">
        <thead>
            <tr>
                {{-- Baris Header Utama --}}
                <th class="col-sign-diajukan" rowspan="2">Diajukan oleh,</th>
                <th class="col-sign-diperiksa" rowspan="2">Diperiksa oleh,</th>
                <th colspan="2">Diketahui oleh,</th> {{-- Gabung 2 kolom sub-diketahui --}}
                <th class="col-sign-diterima" rowspan="2">Diterima Oleh</th>
                <th class="col-sign-budget" rowspan="2">
                    PERIODE BUDGET<br>
                    @php
                        // Ambil tanggal pembuatan FPPB
                        $creationDate = Carbon\Carbon::parse($record->created_at);
                        // Cari hari Senin di minggu tersebut
                        // Carbon::MONDAY memastikan Senin adalah awal minggu
                        $startOfWeekMonday = $creationDate->copy()->startOfWeek(Carbon\Carbon::MONDAY);
                        // Cari hari Sabtu di minggu tersebut (Senin + 5 hari)
                        $endOfWeekSaturday = $startOfWeekMonday->copy()->addDays(5);
                        // Format tanggalnya
                        $startFormatted = $startOfWeekMonday->format('d M Y');
                        $endFormatted = $endOfWeekSaturday->format('d M Y');
                    @endphp
                    {{-- Tampilkan periode Senin s/d Sabtu --}}
                    {{ $startFormatted }} s/d {{ $endFormatted }}
                </th>
            </tr>
            <tr>
                {{-- Baris Sub-Header Diketahui --}}
                <th class="col-sign-diketahui-1">RMPM / NON RMPM</th>
                <th class="col-sign-diketahui-2">NON RMPM</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                {{-- Baris Nama --}}
                <td class="col-sign-diajukan">M. N. Arif</td>
                <td class="col-sign-diperiksa">Gunawan</td>
                <td class="col-sign-diketahui-1">M. Mustofa</td>
                <td class="col-sign-diketahui-2">Albert T.</td>
                <td class="col-sign-diterima">M. Fajarul H.</td>
                <td class="col-sign-budget">&nbsp;</td> {{-- Kolom budget kosong di baris nama --}}
            </tr>
            <tr>
                {{-- Baris Jabatan --}}
                <td class="col-sign-diajukan">Adm.</td>
                <td class="col-sign-diperiksa">Mgr. Engineering</td>
                <td class="col-sign-diketahui-1">FM</td>
                <td class="col-sign-diketahui-2">FAM</td>
                <td class="col-sign-diterima">Purchasing</td>
                <td class="col-sign-budget">&nbsp;</td> {{-- Kolom budget kosong di baris jabatan --}}
            </tr>
        </tbody>
    </table>

</div>

</body>
</html>