<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('incomes', function (Blueprint $t) {
            $t->id();
            $t->string('income_no');
            $t->string('invoice_no')->nullable();
            $t->timestamp('received_at')->useCurrent();
            $t->text('note')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('app_users');
            $t->timestamps();
            $t->unique(['income_no','invoice_no']);
        });

        Schema::create('income_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('income_id')->constrained()->cascadeOnDelete();
            $t->foreignId('part_id')->constrained()->restrictOnDelete();
            $t->foreignId('vendor_id')->nullable()->constrained();
            $t->string('lot_no')->nullable();
            $t->date('expiry_date')->nullable();
            $t->decimal('qty_total',18,3);
            $t->integer('snp_overwrite')->nullable();
            $t->text('remark')->nullable();
        });
    }

    public function down(): void {
        Schema::dropIfExists('income_lines');
        Schema::dropIfExists('incomes');
    }
};
