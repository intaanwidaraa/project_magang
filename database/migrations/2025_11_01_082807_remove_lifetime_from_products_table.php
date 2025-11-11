<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Hapus dua kolom ini
            $table->dropColumn('lifetime_penggunaan');
            $table->dropColumn('tanggal_mulai_pemakaian');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // (Opsional) Jika ingin bisa di-rollback
            $table->integer('lifetime_penggunaan')->default(0)->after('stock');
            $table->date('tanggal_mulai_pemakaian')->nullable()->after('lifetime_penggunaan');
        });
    }
};