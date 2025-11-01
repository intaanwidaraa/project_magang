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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('requester_info')->nullable()->after('supplier_id'); // Sesuaikan posisi 'after' jika perlu
            $table->string('coa_name')->nullable()->after('requester_info');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['requester_info', 'coa_name']);
        });
    }
};
