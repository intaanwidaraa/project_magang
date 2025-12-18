<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Menambahkan kolom is_stock setelah SKU
            // Default 1 (True) artinya dianggap barang Stok
            $table->boolean('is_stock')
                  ->default(true) 
                  ->after('sku')
                  ->comment('1=Stok (Inventory), 0=Non-Stok (Direct/Jasa)');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_stock');
        });
    }
};