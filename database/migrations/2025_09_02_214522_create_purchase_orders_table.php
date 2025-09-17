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
         Schema::create('purchase_orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('supplier_id')->constrained('suppliers');
        $table->string('po_number')->unique();
        $table->json('items');
        $table->text('notes')->nullable();
        $table->enum('payment_method', ['po', 'cash','urgent'])->default('po');
        $table->decimal('grand_total', 15, 2)->default(0);
        $table->string('status')->default('ordered');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
