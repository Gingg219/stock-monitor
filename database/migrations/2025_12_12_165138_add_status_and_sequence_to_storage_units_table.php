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
        Schema::table('storage_units', function (Blueprint $table) {
            $table->enum('status', ['available', 'allocated', 'shipped'])->default('available')->after('qty');
            $table->unsignedBigInteger('sequence')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('storage_units')) {
            Schema::table('storage_units', function (Blueprint $table) {
                // Xóa cột sequence và status nếu chúng tồn tại
                if (Schema::hasColumn('storage_units', 'sequence')) {
                    $table->dropColumn('sequence');
                }
                if (Schema::hasColumn('storage_units', 'status')) {
                    $table->dropColumn('status');
                }
            });
        }
    }
};
