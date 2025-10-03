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
        Schema::table('stock_requisitions', function (Blueprint $table) {
            // Menambahkan kolom 'shift' setelah kolom 'department'
            $table->string('shift')->after('department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_requisitions', function (Blueprint $table) {
            // Menghapus kolom 'shift' jika migrasi di-rollback
            $table->dropColumn('shift');
        });
    }
};