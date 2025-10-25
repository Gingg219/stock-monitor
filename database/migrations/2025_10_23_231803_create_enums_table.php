<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        DB::statement("CREATE TYPE warehouse_mode_enum AS ENUM ('fixed', 'non_fixed')");
        DB::statement("CREATE TYPE movement_type_enum AS ENUM ('inbound','transfer_k1_k2','outbound','adjustment_increase','adjustment_decrease')");
        DB::statement("CREATE TYPE doc_type_enum AS ENUM ('income','transfer','outbound','adjustment')");
        DB::statement("CREATE TYPE storage_unit_type_enum AS ENUM ('pallet','box')");
        DB::statement("CREATE TYPE slot_allowed_unit_enum AS ENUM ('any','pallet','box')");
    }
    public function down(): void {
        DB::statement("DROP TYPE IF EXISTS warehouse_mode_enum CASCADE");
        DB::statement("DROP TYPE IF EXISTS movement_type_enum CASCADE");
        DB::statement("DROP TYPE IF EXISTS doc_type_enum CASCADE");
        DB::statement("DROP TYPE IF EXISTS storage_unit_type_enum CASCADE");
        DB::statement("DROP TYPE IF EXISTS slot_allowed_unit_enum CASCADE");
    }
};
