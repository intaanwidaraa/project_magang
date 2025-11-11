<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Hapus kolom coa_name jika ada
            if (Schema::hasColumn('purchase_orders', 'coa_name')) {
                $table->dropColumn('coa_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Jika di-rollback, tambahkan lagi kolomnya
            $table->string('coa_name')->nullable()->after('requester_info');
        });
    }
};