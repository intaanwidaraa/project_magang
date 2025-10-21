<!DOCTYPE html>
<html>
<head>
    <title>FPPB - {{ $record->po_number }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
       
        @page {
            size: A4 landscape;
            margin: 10mm 10mm 10mm 10mm; 
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 8.5pt;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 0;
            width: 100%;
        }

        
        .header {
            width: 100%;
            display: table;
            table-layout: fixed;
            margin-bottom: 10px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }
        .header-left, .header-right {
            display: table-cell;
            vertical-align: top;
        }
        .header-left {
            width: 60%;
            padding-right: 10px;
        }
        .header-right {
            width: 40%;
            text-align: right;
            border-left: 1px solid #ccc;
            padding-left: 10px;
        }
        .logo {
            float: left;
            margin-right: 10px;
        }
        .logo img {
            height: 40px; 
            width: auto;
        }
        .doc-info table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-left: auto;
            border: 1px solid #000;
        }
        .doc-info table td {
            padding: 2px 5px;
            border: none;
            text-align: left;
        }
        .doc-info table .info-label {
            width: 30%;
            font-weight: bold;
            border-right: 1px solid #000;
        }
        .doc-info table .info-value {
            border-left: 1px solid #000;
        }
        h2 {
            text-align: center;
            margin: 10px 0;
            font-size: 13pt;
        }

        
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed; 
        }
        .item-table th, .item-table td {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
            vertical-align: middle; 
            height: 18px; 
            line-height: 1.2; 
        }
        .item-table th {
            background-color: #f2f2f2;
            font-size: 8pt;
            white-space: normal; 
        }
        
        
        .item-table .col-no { width: 3%; }
        .item-table .col-coa { width: 4%; }
        .item-table .col-sku { width: 7%; }
        .item-table .col-name { width: 17%; text-align: left; } 
        .item-table .col-qty, .item-table .col-sat, .item-table .col-stock { width: 4%; }
        .item-table .col-price, .item-table .col-total { width: 8.5%; text-align: right !important; } 
        .item-table .col-keterangan { width: 15%; font-size: 7.5pt; text-align: left; }
        .item-table .col-last-buy { width: 9%; } 
        .item-table .col-check, .item-table .col-cash, .item-table .col-noncash, .item-table .col-urgent { width: 3%; }
       


        .total-row td {
            border: none !important;
            padding: 5px;
            font-weight: bold;
            text-align: right;
            border-top: 1px solid #000 !important; 
        }
        .total-row .total-label {
            text-align: left;
            border-right: 1px solid #000 !important;
            font-weight: normal;
        }
        .total-row .total-value {
             border-left: 1px solid #000 !important; 
             border-right: 1px solid #000 !important;
             text-align: right;
        }

        
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 8pt;
        }
        .sign-table td {
            border: 1px solid #000;
            padding: 5px 3px;
            height: 40px; 
            vertical-align: top;
            text-align: center;
            width: 20%; 
        }
        .sign-table .sign-label {
            height: 15px;
            font-weight: bold;
            padding-bottom: 0;
        }
        .sign-table .sign-name {
            height: 15px;
            font-weight: normal;
        }
        .budget-row {
            margin-top: 10px;
            text-align: right;
            font-weight: bold;
            font-size: 8.5pt;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div class="header-left">
            <div class="logo">
                {{-- GANTI PATH INI DENGAN PATH LOGO PERUSAHAAN ANDA YANG BENAR --}}
                <img src="{{ public_path('images/Logo_MAS.png') }}" alt="Logo Perusahaan"> 
            </div>
            <div style="float: left;">
                <p style="font-size: 11pt; font-weight: bold; margin: 0;">PT. MAKMUR ARTHA SEJAHTERA</p>
                <p style="font-size: 8pt; margin: 0;">JL. KI Ageng Tapa Blok Nambo No. 168 Astapada</p>
                <p style="font-size: 8pt; margin: 0;">Tengah Tani, Cirebon</p>
            </div>
        </div>
        <div class="header-right">
            <table class="doc-info">
                <tr>
                    <td class="info-label">Tanggal</td>
                    <td class="info-value">: **{{ $record->created_at->format('d/m/Y') }}**</td>
                </tr>
                <tr>
                    <td class="info-label">Nomor</td>
                    <td class="info-value">: **{{ $record->po_number }}**</td>
                </tr>
                <tr>
                    <td class="info-label">Dept / Cost Center</td>
                    {{-- Sesuaikan jika Anda memiliki kolom dinamis untuk ini --}}
                    <td class="info-value">: **ENGINEERING / 450** </td> 
                </tr>
            </table>
        </div>
    </div>

    <h2>Form Permintaan Pembelian Barang</h2>

    {{-- TABEL RINCIAN BARANG --}}
    <table class="item-table">
        <thead>
            <tr>
                <th class="col-no">NO</th>
                <th class="col-coa">COA</th>
                <th class="col-sku">Kode Barang</th>
                <th class="col-name">Nama Barang</th>
                <th class="col-qty">Qty</th>
                <th class="col-sat">Sat</th>
                <th class="col-stock">Stock di Gudang</th>
                <th class="col-price">Estimasi Harga Satuan (Rp)</th>
                <th class="col-total">Total Estimasi (Rp)</th>
                <th class="col-keterangan">Keterangan</th>
                <th class="col-last-buy">Terakhir dibeli / Kedatangan</th>
                <th class="col-check">Check (√)</th>
                <th class="col-cash">PO / CASH</th>
                <th class="col-noncash">NON PO / NON CASH</th>
                <th class="col-urgent">URGENT</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotal = 0;
                $itemsCollection = collect($record->items);
                $itemCount = $itemsCollection->count();
            @endphp
            @foreach ($itemsCollection as $item)
                @php
                    $supplierItem = \App\Models\SupplierItem::find($item['supplier_item_id']);
                    $product = optional($supplierItem)->product;
                    
                    $total = ($item['quantity'] ?? 0) * ($item['price'] ?? 0);
                    $grandTotal += $total;

                    $poOrCash = strtoupper($record->payment_method ?? 'PO'); 
                    $lastArrival = 'N/A'; 
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="col-coa">N/A</td> 
                    <td class="col-sku">{{ optional($product)->sku ?? 'N/A' }}</td>
                    <td class="col-name">{{ optional($supplierItem)->nama_item ?? 'Produk tidak ditemukan' }}</td>
                    <td class="col-qty">{{ $item['quantity'] ?? 0 }}</td>
                    <td class="col-sat">{{ $item['unit'] ?? 'pcs' }}</td>
                    <td class="col-stock">{{ optional($product)->stock ?? 0 }}</td>
                    <td class="col-price">{{ number_format($item['price'] ?? 0, 0, ',', '.') }}</td>
                    <td class="col-total">{{ number_format($total, 0, ',', '.') }}</td>
                    <td class="col-keterangan">{{ $record->notes ?? '-' }}</td>
                    <td class="col-last-buy">{{ $lastArrival }}</td>
                    <td class="col-check">&nbsp;</td> 
                    <td class="col-cash">{{ $poOrCash === 'PO' || $poOrCash === 'CASH' ? '√' : '' }}</td>
                    <td class="col-noncash">{{ $poOrCash !== 'PO' && $poOrCash !== 'CASH' ? '√' : '' }}</td>
                    <td class="col-urgent">{{ $poOrCash === 'URGENT' ? '√' : '' }}</td>
                </tr>
            @endforeach
            
            {{-- Isi baris kosong hingga minimal 10 baris --}}
            @for ($i = $itemCount; $i < 10; $i++)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="col-coa">&nbsp;</td>
                    <td class="col-sku">&nbsp;</td>
                    <td class="col-name">&nbsp;</td>
                    <td class="col-qty">&nbsp;</td>
                    <td class="col-sat">&nbsp;</td>
                    <td class="col-stock">&nbsp;</td>
                    <td class="col-price">&nbsp;</td>
                    <td class="col-total">&nbsp;</td>
                    <td class="col-keterangan">&nbsp;</td>
                    <td class="col-last-buy">&nbsp;</td>
                    <td class="col-check">&nbsp;</td>
                    <td class="col-cash">&nbsp;</td>
                    <td class="col-noncash">&nbsp;</td>
                    <td class="col-urgent">&nbsp;</td>
                </tr>
            @endfor
            
            <tr class="total-row">
                <td colspan="8" class="total-label" style="text-align: left; font-weight: normal;">Total</td>
                <td colspan="1" class="total-value" style="font-weight: bold; text-align: right;">Rp **{{ number_format($grandTotal, 0, ',', '.') }}**</td>
                <td colspan="6" style="border: none;">&nbsp;</td>
            </tr>
        </tbody>
    </table>

    {{-- TABEL TANDA TANGAN --}}
    <table class="sign-table">
        <thead>
            <tr>
                <td class="sign-label">Diajukan oleh, <br> Adm.</td>
                <td class="sign-label">Diperiksa oleh, <br> Mgr. Engineering</td>
                <td class="sign-label" colspan="2">Diketahui oleh, <br> RM/PM</td>
                <td class="sign-label">Diterima oleh, <br> Purchasing</td>
            </tr>
            <tr style="height: 15px;">
                <td class="sign-name">M. N. Arif</td>
                <td class="sign-name">Gunawan</td>
                <td class="sign-name" style="width: 10%;">M. Mustofa (RM/PM)</td>
                <td class="sign-name" style="width: 10%;">Albert T. (NON RM/PM)</td>
                <td class="sign-name">M. Fajarul H.</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                {{-- Ruang untuk Tanda Tangan --}}
                <td><br><br><br></td> 
                <td><br><br><br></td>
                <td colspan="2"><br><br><br></td>
                <td><br><br><br></td>
            </tr>
        </tbody>
    </table>
    
    <div class="budget-row">
        PERIODE BUDGET: {{ Carbon\Carbon::parse($record->created_at)->startOfWeek()->format('d M Y') }} s/d {{ Carbon\Carbon::parse($record->created_at)->endOfWeek()->format('d M Y') }}
    </div>

</div>

</body>
</html>