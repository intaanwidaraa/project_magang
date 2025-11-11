<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CategorizeExistingProducts extends Command
{
    /**
     * 
     * @var string
     */
    protected $signature = 'products:categorize';

    /**
     * 
     * @var string
     */
    protected $description = 'Mengkategorikan produk yang ada sebagai consumable atau non-consumable (alat) berdasarkan kata kunci.';

    protected $alatKeywords = [
        'ACTUATOR',
        'ALARM',
        'APAR',
        'ARM',
        'BLOWER',
        'BRAKET',
        'BRACKET',
        'BREAKER',
        'BUTTON',
        'CONTACTOR',
        'CONTROL',
        'CONTROLLER',
        'CONVEYOR',
        'COUPLING',
        'CYLINDER',
        'DINAMO',
        'DONGKRAK',
        'DRIVE',
        'DUCTING',
        'DUDUKAN',
        'ENCODER',
        'EXHAUST',
        'FAN',
        'FITTING LAMPU',
        'FLANGE',
        'FRL',
        'GEARBOX',
        'GEMBOK',
        'GENSET',
        'HANDLE',
        'HOLDER',
        'HOUSING',
        'INVERTER',
        'KIPAS',
        'KLAKSON',
        'KOMPRESOR',
        'KONTAK',
        'KONTAKTOR',
        'KOPLING',
        'KOTAK OBAT',
        'LAMPU UV',
        'LOCK',
        'MCB',
        'MCCB',
        'MESIN',
        'METER',
        'MIKI PULLEY',
        'MODUL',
        'MODULE',
        'MOLDING',
        'MOTOR',
        'OVERLOAD',
        'PANEL',
        'PCB',
        'PEMADAM',
        'PILLOW BLOCK',
        'PISTON',
        'POMPA',
        'POWER SUPPLY',
        'PRESSURE',
        'PRINTER',
        'PUMP',
        'REGULATOR',
        'RELAY',
        'RUMAH',
        'SAKLAR',
        'SEGITIGA PENGAMAN',
        'SELENOID',
        'SENSOR',
        'SERVO',
        'SHOCK BEKER',
        'SOCKET',
        'SSR',
        'STABILIZER',
        'SWITCH',
        'TERMINAL',
        'TESTER',
        'THERMOMETER',
        'TIE ROD',
        'TIMER',
        'TOMBOL',
        'TRAFO',
        'TRANSFORMERS',
        'TRAY',
        'TROLI',
        'TROLLEY',
        'VALVE'
    ];


    public function handle()
    {
        $this->info('Memulai pengelompokan produk berdasarkan data Anda...');

        Product::query()->update(['is_consumable' => true]);
        $this->info('Semua produk telah di-set sebagai Habis Pakai (default).');

        $this->info('Mencari Aset/Alat (Non-Consumable) berdasarkan kata kunci...');
        
        $nonConsumableCount = 0;
        $alatKeywords = array_map('strtolower', $this->alatKeywords); 

        $products = Product::query()->cursor();
        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        foreach ($products as $product) {
            $productNameLower = strtolower($product->name);
            
            foreach ($alatKeywords as $keyword) {
                if (Str::contains($productNameLower, $keyword)) {
                    $product->is_consumable = false;
                    $product->save();
                    
                    $nonConsumableCount++;
                    break; 
                }
            }
            $bar->advance();
        }

        $bar->finish();
        $this->info("\n"); 
        $this->info("=================== SELESAI ===================");
        $this->info("Total produk diperiksa: " . $products->count());
        $this->info("Berhasil mengidentifikasi {$nonConsumableCount} produk sebagai ALAT (Non-Consumable).");
        $this->info("Dashboard Anda sekarang akan menampilkan stok kritis yang sudah terfilter.");

        return 0;
    }
}