<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('movements', function (Blueprint $t) {
            $t->id();
            $t->enum('doc_type', ['income','transfer','outbound','adjustment']);
            $t->enum('movement_type', ['inbound','transfer_k1_k2','outbound','adjustment_increase','adjustment_decrease']);
            $t->foreignId('from_warehouse_id')->nullable()->constrained('warehouses');
            $t->foreignId('to_warehouse_id')->nullable()->constrained('warehouses');
            $t->foreignId('created_by')->nullable()->constrained('app_users');
            $t->string('ref_no')->nullable();
            $t->text('note')->nullable();
            $t->timestamps();
        });

        Schema::create('movement_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('movement_id')->constrained()->cascadeOnDelete();
            $t->foreignId('part_id')->constrained()->restrictOnDelete();
            $t->decimal('qty',18,3);
            $t->foreignId('from_slot_id')->nullable()->constrained('slots');
            $t->foreignId('to_slot_id')->nullable()->constrained('slots');
            $t->foreignId('storage_unit_id')->nullable()->constrained('storage_units');
            $t->string('lot_no')->nullable();
            $t->date('expiry_date')->nullable();
        });
    }

    public function down(): void {
        Schema::dropIfExists('movement_lines');
        Schema::dropIfExists('movements');
    }
};
