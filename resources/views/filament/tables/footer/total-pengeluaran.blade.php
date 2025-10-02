@php
    // Variabel $records (berisi data tabel yang sudah difilter)
    // otomatis tersedia di file view ini oleh Filament.
    $total = $records->sum('grand_total');
@endphp

<div class="p-4 bg-gray-200 dark:bg-gray-800 rounded-b-xl">
    <div class="flex justify-end items-center">
        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200 mr-4">
            Total Keseluruhan:
        </span>
        <span class="text-lg font-bold text-gray-900 dark:text-white">
            Rp {{ number_format($total, 0, ',', '.') }}
        </span>
    </div>
</div>