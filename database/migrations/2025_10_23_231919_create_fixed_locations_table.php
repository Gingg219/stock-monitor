<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('fixed_locations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $t->foreignId('part_id')->constrained()->restrictOnDelete();
            $t->foreignId('slot_id')->constrained()->restrictOnDelete();
            $t->decimal('min_qty', 18, 3)->default(0);
            $t->decimal('max_qty', 18, 3)->default(0);
            $t->unique(['warehouse_id','part_id','slot_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('fixed_locations');
    }
};
