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

        
        .header-table {
            width: 100%;
            border-bottom: 1.5px solid #000; 
            padding-bottom: 5px;
            margin-bottom: 10px;
            border-collapse: collapse; 
        }
        .header-table td {
            vertical-align: top; 
            padding: 0;
        }
        .header-table .logo-info-cell {
            width: 60%; 
            padding-top: 0px; 
        }
        .header-table .doc-info-cell {
            width: 40%; 
            padding-top: 0px; 
        }

        
        .logo-section {
            display: flex; 
            align-items: flex-start; 
            margin-bottom: 5px; 
        }
        .logo-img-container {
            width: 70px; 
            flex-shrink: 0; 
            margin-right: 10px; 
            padding-top: 0px; 
        }
        .logo-img-container img {
            height: 60px; 
            width: auto;
            display: block;
        }
        .company-info {
            flex-grow: 1; 
        }
        .company-info p {
            margin: 0;
            line-height: 1.2; 
        }
        .company-info .company-name {
            font-size: 10pt; 
            font-weight: bold;
        }
        .makmur-group-text {
            font-size: 12pt;
            font-weight: bold;
            color: #1a73e8; 
            margin-top: 0px;
            margin-left: 70px; 
            line-height: 1;
        }
        .food-beverage-text {
            font-size: 7pt;
            font-weight: normal;
            margin-top: 2px;
            margin-left: 70px; 
            line-height: 1;
            display: block;
        }

        
        .makmur-group-line {
            width: 100%;
            height: 1px;
            background-color: #000;
            margin-top: 5px; 
        }


        .doc-info table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            border: 1px solid #000;
        }
        .doc-info td {
            padding: 1.5px 4px; 
            vertical-align: middle; 
            border: 1px solid #000;
            height: 16px; 
        }
        .doc-info .label {
            width: 35%;
            border-right: 1px solid #000;
            text-align: left;
        }
        .doc-info .value {
            width: 65%;
            font-weight: bold;
            text-align: left;
            padding-left: 5px;
        }

        
        h2 {
            text-align: center;
            margin: 8px 0 12px 0;
            font-size: 12pt;
            text-decoration: underline;
            font-weight: bold;
        }

       
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
            table-layout: fixed;
            font-size: 7pt;
        }
        .item-table th, .item-table td {
            border: 1px solid #000;
            padding: 1.5px;
            text-align: center;
            vertical-align: middle;
            height: 15px;
            line-height: 1.1;
            word-wrap: break-word;
        }
        .item-table thead th {
            background-color: #E0E0E0;
            font-weight: bold;
            white-space: normal;
        }
        .item-table tbody td {
             height: 15px;
             background-color: #fff;
        }
        .item-table .text-left { text-align: left; padding-left: 3px; }
        .item-table .text-right { text-align: right; padding-right: 3px; }

        
        .item-table .col-no { width: 2.5%; }
        .item-table .col-coa { width: 6%; }
        .item-table .col-sku { width: 7%; }
        .item-table .col-name { width: 15%; }
        .item-table .col-qty { width: 3%; }
        .item-table .col-sat { width: 4%; }
        .item-table .col-stock { width: 4%; }
        .item-table .col-price { width: 7%; }
        .item-table .col-total { width: 7%; }
        .item-table .col-keterangan { width: 12%; }
        .item-table .col-last-buy { width: 7%; }
        .item-table .col-check { width: 2.5%; }
        .item-table .col-po-cash { width: 5%; }
        .item-table .col-supplier { width: 9%; }
        .item-table .col-nonpo { width: 5%; }
        .item-table .col-urgent { width: 4%; }

        
         .item-table tfoot td {
            border: 1px solid #000;
            padding: 2px;
            font-weight: bold;
            height: 15px;
            background-color: #E0E0E0;
        }
        .item-table tfoot .total-label {
            text-align: right;
            padding-right: 10px;
        }
         .item-table tfoot .rp-label {
             text-align: left;
             padding-left: 3px;
             border-right: none !important;
         }
         .item-table tfoot .total-amount {
             text-align: right;
             padding-right: 3px;
             border-left: none !important;
         }

        
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            font-size: 7.5pt;
        }
        .sign-table td {
            border: 1px solid #000;
            padding: 1px 3px;
            height: 45px;
            vertical-align: top;
            text-align: center;
        }
         .sign-table .sign-label {
            height: auto;
            font-weight: bold;
            padding-bottom: 0;
            margin-bottom: 2px;
            line-height: 1.1;
        }
        .sign-table .sign-name {
            height: auto;
            font-weight: normal;
            vertical-align: bottom;
            padding-top: 25px;
        }
        .sign-table .col-aju { width: 20%; }
        .sign-table .col-periksa { width: 20%; }
        .sign-table .col-ketahui-1 { width: 20%; }
        .sign-table .col-ketahui-2 { width: 20%; }
        .sign-table .col-terima { width: 20%; }

        
        .budget-row {
            margin-top: 5px;
            text-align: right;
            font-weight: bold;
            font-size: 8pt;
        }
    </style>
</head>
<body>

<div class="container">
    {{-- HEADER MENGGUNAKAN TABLE --}}
    <table class="header-table">
        <tr>
            <td class="logo-info-cell">
                <div class="logo-section">
                    <div class="logo-img-container">
                        @php $logoPath = public_path('images/Logo_MAS.png'); @endphp
                        @if(file_exists($logoPath))
                            {{-- Menggunakan logo ikon saja --}}
                            <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" alt="Logo">
                        @endif
                    </div>
                    <div class="company-info">
                        <p class="company-name">PT. MAKMUR ARTHA SEJAHTERA</p>
                        <p>JL. KI Ageng Tapa Blok Nambo No. 168 Astapada</p>
                        <p>Tengah Tani, Cirebon</p>
                    </div>
                </div>
                {{-- Teks "MAKMUR GROUP" dan "FOOD AND BEVERAGE COMPANY" --}}
                <div class="makmur-group-text">MAKMUR GROUP</div>
                <div class="food-beverage-text">FOOD AND BEVERAGE COMPANY</div>
            </td>
            <td class="doc-info-cell">
                <table class="doc-info">
                    <tr>
                        <td class="label">Tanggal</td>
                        <td class="value">{{ $record->created_at->format('d / m / Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Nomor</td>
                        <td class="value">{{ $record->po_number }}</td>
                    </tr>
                    <tr>
                        <td class="label">Dept / Cost Center</td>
                        <td class="value">ENGINEERING / 450</td> {{-- Ganti dinamis jika perlu --}}
                    </tr>
                </table>
            </td>
        </tr>
    </table>

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
                <th class="col-stock" rowspan="2">Stock di Gudang</th>
                <th colspan="2">Estimasi Harga</th> {{-- Gabung 2 kolom --}}
                <th class="col-keterangan" rowspan="2">Keterangan</th>
                <th class="col-last-buy" rowspan="2">Terakhir Beli / Kedatangan</th>
                <th class="col-check" rowspan="2">(√)</th>
                <th class="col-po-cash" rowspan="2">PO / CASH</th>
                <th class="col-supplier" rowspan="2">SUPPLIER</th>
                <th class="col-nonpo" rowspan="2">NON PO / NON CASH</th>
                <th class="col-urgent" rowspan="2">URGENT</th>
            </tr>
            {{-- Baris 2: Sub-header Estimasi Harga --}}
            <tr>
                <th class="col-price">Harga</th>
                <th class="col-total">Total</th>
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
                    $lastArrival = 'N/A'; // Perlu logika tambahan
                    $supplierName = $record->supplier->name ?? 'N/A';
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="col-coa">N/A</td>
                    <td class="col-sku">{{ optional($product)->sku ?? 'N/A' }}</td>
                    <td class="col-name text-left">{{ optional($supplierItem)->nama_item ?? 'Produk Tdk Ditemukan' }}</td>
                    <td class="col-qty">{{ $item['quantity'] ?? 0 }}</td>
                    <td class="col-sat">{{ $item['unit'] ?? 'pcs' }}</td>
                    <td class="col-stock">{{ optional($product)->stock ?? 0 }}</td>
                    <td class="col-price text-right">{{ number_format($item['price'] ?? 0, 0, ',', '.') }}</td>
                    <td class="col-total text-right">{{ number_format($total, 0, ',', '.') }}</td>
                    <td class="col-keterangan text-left">{{ $record->notes ?? '-' }}</td>
                    <td class="col-last-buy">{{ $lastArrival }}</td>
                    <td class="col-check">√</td>
                    <td class="col-po-cash">{{ ($paymentMethod === 'PO' || $paymentMethod === 'CASH') ? $paymentMethod : '' }}</td>
                    <td class="col-supplier text-left">{{ $supplierName }}</td>
                    <td class="col-nonpo">{{ ($paymentMethod !== 'PO' && $paymentMethod !== 'CASH' && $paymentMethod !== 'URGENT') ? '??' : '' }}</td>
                    <td class="col-urgent">{{ ($paymentMethod === 'URGENT') ? '√' : '' }}</td>
                </tr>
            @endforeach

            {{-- Baris Kosong --}}
            @for ($i = $rowCount; $i < $minRows; $i++)
                <tr>
                    <td>{{ $i + 1 }}</td> <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td>
                    <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td>
                    <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor
        </tbody>
        <tfoot>
             {{-- Baris Total --}}
             <tr>
                <td colspan="7" class="total-label">Total</td>
                <td class="rp-label">Rp</td>
                <td class="total-amount">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                <td colspan="7"></td>
             </tr>
        </tfoot>
    </table>

    {{-- TABEL TANDA TANGAN --}}
    <table class="sign-table">
         <thead>
             <tr>
                 <td class="sign-label col-aju">Diajukan oleh,<br>Adm.</td>
                 <td class="sign-label col-periksa">Diperiksa oleh,<br>Mgr. Engineering</td>
                 <td class="sign-label col-ketahui-1">Diketahui oleh,<br>RM/PM</td>
                 <td class="sign-label col-ketahui-2">Diketahui oleh,<br>NON RM/PM</td>
                 <td class="sign-label col-terima">Diterima oleh,<br>Purchasing</td>
             </tr>
         </thead>
         <tbody>
             <tr>
                 <td class="sign-name col-aju">M. N. Arif</td>
                 <td class="sign-name col-periksa">Gunawan</td>
                 <td class="sign-name col-ketahui-1">M. Mustofa</td>
                 <td class="sign-name col-ketahui-2">Albert T.</td>
                 <td class="sign-name col-terima">M. Fajarul H.</td>
             </tr>
         </tbody>
    </table>

    <div class="budget-row">
        PERIODE BUDGET: {{ Carbon\Carbon::parse($record->created_at)->startOfWeek()->format('d M Y') }} s/d {{ Carbon\Carbon::parse($record->created_at)->endOfWeek()->format('d M Y') }}
    </div>

</div>

</body>
</html>