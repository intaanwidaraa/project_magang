<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_consumable')
                  ->default(true) // <-- Default-nya 'true' (Habis Pakai)
                  ->after('unit')
                  ->comment('True = Habis Pakai, False = Alat/Aset');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_consumable');
        });
    }
};