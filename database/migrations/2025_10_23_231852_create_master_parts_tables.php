<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vendors', function (Blueprint $t) {
            $t->id();
            $t->string('code')->nullable()->unique();
            $t->string('name');
        });

        Schema::create('parts', function (Blueprint $t) {
            $t->id();
            $t->string('part_no')->unique();
            $t->string('name')->nullable();
            $t->foreignId('vendor_id')->nullable()->constrained();
            $t->integer('snp')->nullable();
            $t->boolean('has_expiry')->default(true);
            $t->integer('expiry_days')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('parts');
        Schema::dropIfExists('vendors');
    }
};
