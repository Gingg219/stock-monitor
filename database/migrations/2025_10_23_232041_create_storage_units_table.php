<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('storage_units', function (Blueprint $t) {
            $t->id();
            $t->enum('unit_type', ['pallet','box']);
            $t->string('unit_code')->unique();
            $t->foreignId('income_line_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('part_id')->constrained()->restrictOnDelete();
            $t->string('lot_no')->nullable();
            $t->date('expiry_date')->nullable();
            $t->decimal('qty',18,3);
            $t->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $t->foreignId('current_slot_id')->nullable()->constrained('slots')->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('storage_units');
    }
};
