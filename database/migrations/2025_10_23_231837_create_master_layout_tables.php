<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('warehouses', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->enum('mode', ['fixed','non_fixed']);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('racks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $t->string('code');
            $t->unique(['warehouse_id','code']);
        });

        Schema::create('tiers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('rack_id')->constrained()->cascadeOnDelete();
            $t->integer('level_no');
            $t->unique(['rack_id','level_no']);
        });

        Schema::create('slots', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tier_id')->constrained()->cascadeOnDelete();
            $t->string('code');
            $t->enum('allowed_unit', ['any','pallet','box'])->default('any');
            $t->boolean('is_active')->default(true);
            $t->unique(['tier_id','code']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('slots');
        Schema::dropIfExists('tiers');
        Schema::dropIfExists('racks');
        Schema::dropIfExists('warehouses');
    }
};
