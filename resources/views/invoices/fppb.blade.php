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

        /* --- HEADER TABLE --- */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px; 
            table-layout: fixed;
        }
        .header-table td {
            vertical-align: top;
            padding: 0;
            font-size: 8pt; 
            line-height: 1.3;
        }
        .header-left-col { width: 60%; }
        .header-right-col { width: 40%; }
        
        .company-info-table { border-collapse: collapse; }
        .company-info-table .logo-cell {
            width: 60px;
            padding-right: 10px;
            vertical-align: top;
        }
        .company-info-table .logo-cell img {
            height: 50px;
            width: auto;
            display: block;
        }
        .company-info-table .info-cell { vertical-align: top; }
        .company-info-table .company-name {
            font-size: 10pt;
            font-weight: bold;
            margin: 0 0 2px 0;
            padding: 0;
        }
        .company-info-table .company-address { margin: 0; padding: 0; line-height: 1.3; }
        
        .doc-info-table {
            border-collapse: collapse;
            width: auto;
            margin-left: auto;
        }
        .doc-info-table td {
            padding-bottom: 2px;
            font-size: 8pt;
            line-height: 1.3;
        }
        .doc-info-table .label {
            text-align: left;
            padding-right: 5px;
            white-space: nowrap; 
        }
        .doc-info-table .separator { text-align: left; width: 5px; }
        .doc-info-table .value { text-align: left; font-weight: bold; }
        
        h2 {
            text-align: center;
            margin: 8px 0 12px 0;
            font-size: 12pt;
            font-weight: bold;
        }

        /* --- ITEM TABLE --- */
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
        
        /* Column Widths */
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
        
        .item-table tfoot td {
            border: 1px solid #000;
            padding: 2px;
            font-weight: bold;
            height: 15px;
            background-color: #E0E0E0;
        }
        .item-table tfoot .total-label { text-align: right; padding-right: 10px; }
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

        /* --- SIGN TABLE --- */
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .sign-table th, .sign-table td {
            border: 1px solid #000;
            padding: 2px 3px;
            text-align: center;
            vertical-align: middle;
            font-size: 7pt;
            line-height: 1.1;
        }
        .sign-table thead th {
            font-weight: bold;
            vertical-align: middle;
        }
        .sign-table tbody td {
            height: 40px;
            vertical-align: bottom;
            padding-bottom: 2px;
        }
        .sign-table tbody tr:last-child td {
            height: auto;
            vertical-align: top;
            padding-bottom: 2px;
            padding-top: 1px;
            font-size: 6.5pt;
        }
        .sign-table .col-sign-diajukan { width: 14%; }
        .sign-table .col-sign-diperiksa { width: 14%; }
        .sign-table .col-sign-diketahui-1 { width: 14%; }
        .sign-table .col-sign-diketahui-2 { width: 14%; }
        .sign-table .col-sign-diterima { width: 14%; }
        .sign-table .col-sign-budget { width: 30%; }
    </style>
</head>
<body>

<div class="container">
    
    <table class="header-table">
        <tr>
            <td class="header-left-col">
                <table class="company-info-table">
                    <tr>
                        <td class="logo-cell">
                            @php $logoPath = public_path('images/Logo_MAS.png'); @endphp
                            @if(file_exists($logoPath))
                                <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" alt="Logo MAS">
                            @endif
                        </td>
                        <td class="info-cell">
                            <p class="company-name">PT. MAKMUR ARTHA SEJAHTERA</p>
                            <p class="company-address">JL. KI Ageng Tapa Blok Nambo No. 168 Astapada</p>
                            <p class="company-address">Tengah Tani, Cirebon</p>
                        </td>
                    </tr>
                </table>
            </td>
            
            <td class="header-right-col">
                <table class="doc-info-table">
                    <tr>
                        <td class="label">Tanggal</td>
                        <td class="separator">:</td>
                        <td class="value">{{ $record->created_at->format('d / m / Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Nomor</td>
                        <td class="separator">:</td>
                        <td class="value">{{ $record->po_number }}</td>
                    </tr>
                    <tr>
                        <td class="label">Dept / Cost Centre</td>
                        <td class="separator">:</td>
                        <td class="value">{{ $record->requester_info ?? 'N/A' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    
    <h2>Form Permintaan Pembelian Barang</h2>

    <table class="item-table">
        <thead>
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
                <th colspan="2" style="font-family: DejaVu Sans, sans-serif;">(&#10003;)</th>
                <th class="col-po-cash" rowspan="2">PO / CASH</th>
                <th class="col-supplier" rowspan="2">SUPPLIER</th>
                <th class="col-nopo-tgl" rowspan="2">NO PO / TGL PO</th>
                <th class="col-urgent" rowspan="2">URGENT</th>
            </tr>
            <tr>
                <th class="col-price">Harga</th>
                <th class="col-total">Total</th>
                <th class="col-stock-check">Stock</th>
                <th class="col-non-check">Non</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotal = 0;
                $itemsCollection = collect($record->items); // $record->items sekarang berisi array item JSON
                $rowCount = $itemsCollection->count();
                $minRows = 15; 
                
                // Ambil semua supplier_item_id dari JSON items
                $supplierItemIds = $itemsCollection->pluck('supplier_item_id')->unique()->filter();
                
                // Load SupplierItem beserta relasi Supplier-nya agar bisa ambil nama supplier
                $supplierItems = \App\Models\SupplierItem::with(['product', 'supplier']) // Load relasi supplier
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

                    // PERBAIKAN UTAMA: Ambil nama supplier dari item, bukan dari record utama
                    // Jika user manual ganti supplier di repeater, kita harusnya ambil ID dari item['supplier_id']
                    // Tapi kalau cuma ngandelin relasi supplierItem->supplier juga bisa.
                    // Lebih akurat ambil dari ID yang tersimpan di JSON item['supplier_id'] jika ada.
                    
                    $supplierName = 'N/A';
                    if (!empty($item['supplier_id'])) {
                        $supp = \App\Models\Supplier::find($item['supplier_id']);
                        $supplierName = $supp ? $supp->name : 'N/A';
                    } elseif ($supplierItem && $supplierItem->supplier) {
                         $supplierName = $supplierItem->supplier->name;
                    }
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="col-coa text-left">
                        @php
                            $coaDisplay = 'N/A';
                            $itemCoaName = $item['coa_name'] ?? null; 
                            if (!empty($itemCoaName)) {
                                if (preg_match('/\((.*?)\)/', $itemCoaName, $matches)) {
                                    $coaDisplay = $matches[1]; 
                                } else {
                                    $coaDisplay = 'N/A';
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
                    <td class="col-last-buy">
                        {{ $lastArrivalDates[optional($product)->id] ?? 'N/A' }}
                    </td>

                    @php
                        $isStockItem = $item['is_consumable'] ?? false; 
                    @endphp
                    <td class="col-stock-check" style="font-family: DejaVu Sans, sans-serif;">{{ $isStockItem ? '√' : '' }}</td>
                    <td class="col-non-check" style="font-family: DejaVu Sans, sans-serif;">{{ !$isStockItem ? '√' : '' }}</td>
                    
                    <td class="col-po-cash">{{ ($paymentMethod === 'PO' || $paymentMethod === 'CASH') ? $paymentMethod : '' }}</td>
                    
                    <td class="col-supplier text-left">{{ $supplierName }}</td>
                    
                    <td class="col-nopo-tgl">--</td>
                    <td class="col-urgent">{{ ($paymentMethod === 'URGENT') ? '√' : '' }}</td>
                </tr>
            @endforeach

            {{-- Baris Kosong --}}
            @for ($i = $rowCount; $i < $minRows; $i++)
                <tr>
                    <td>{{ $i + 1 }}</td> <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td>
                    <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td>
                    <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td>
                    <td>&nbsp;</td> <td>&nbsp;</td>
                </tr>
            @endfor
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="total-label">Total</td>
                <td class="rp-label">Rp</td>
                <td class="total-amount">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                <td colspan="8"></td>
            </tr>
        </tfoot>
    </table>

    <table class="sign-table">
        <thead>
            <tr>
                <th class="col-sign-diajukan" rowspan="2">Diajukan oleh,</th>
                <th class="col-sign-diperiksa" rowspan="2">Diperiksa oleh,</th>
                <th colspan="2">Diketahui oleh,</th>
                <th class="col-sign-diterima" rowspan="2">Diterima Oleh</th>
                <th class="col-sign-budget" rowspan="2">
                    PERIODE BUDGET<br>
                    @php
                        $startDate = !empty($record->budget_start_date) ? \Carbon\Carbon::parse($record->budget_start_date) : null;
                        $endDate = !empty($record->budget_end_date) ? \Carbon\Carbon::parse($record->budget_end_date) : null;
                        $startFormatted = $startDate ? $startDate->format('d M Y') : 'N/A';
                        $endFormatted = $endDate ? $endDate->format('d M Y') : 'N/A';
                    @endphp
                    {{ $startFormatted }} s/d {{ $endFormatted }}
                </th>
            </tr>
            <tr>
                <th class="col-sign-diketahui-1">RMPM / NON RMPM</th>
                <th class="col-sign-diketahui-2">NON RMPM</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="col-sign-diajukan">M. N. Aef</td>
                <td class="col-sign-diperiksa">Gunawan</td>
                <td class="col-sign-diketahui-1"></td>
                <td class="col-sign-diketahui-2">Rudi Supriadi</td>
                <td class="col-sign-diterima">M. Faqih Haekal</td>
                <td class="col-sign-budget">&nbsp;</td>
            </tr>
            <tr>
                <td class="col-sign-diajukan">Adm.</td>
                <td class="col-sign-diperiksa">Mgr. Engineering</td>
                <td class="col-sign-diketahui-1">FM</td>
                <td class="col-sign-diketahui-2">FAM</td>
                <td class="col-sign-diterima">Purchasing</td>
                <td class="col-sign-budget">&nbsp;</td>
            </tr>
        </tbody>
    </table>

</div>

</body>
</html>