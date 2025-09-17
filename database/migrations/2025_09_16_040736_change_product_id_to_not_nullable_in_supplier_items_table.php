<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('supplier_items', function (Blueprint $table) {
            // Mengubah kolom agar tidak bisa null (NOT NULL)
            $table->foreignId('product_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_items', function (Blueprint $table) {
            // Mengembalikan kolom agar bisa null lagi jika di-rollback
            $table->foreignId('product_id')->nullable()->change();
        });
    }
};