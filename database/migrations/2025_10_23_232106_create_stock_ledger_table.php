<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('stock_ledger', function (Blueprint $t) {
            $t->id();
            $t->timestamp('occurred_at')->useCurrent();
            $t->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $t->foreignId('slot_id')->nullable()->constrained('slots');
            $t->foreignId('part_id')->constrained()->restrictOnDelete();
            $t->decimal('qty_change',18,3);
            $t->foreignId('movement_line_id')->constrained('movement_lines')->cascadeOnDelete();
        });

        // Trigger auto post movement -> ledger
        DB::unprepared("
        CREATE OR REPLACE FUNCTION fn_post_movement_line()
        RETURNS TRIGGER AS $$
        DECLARE v_from_wh BIGINT; v_to_wh BIGINT;
        BEGIN
          IF NEW.from_slot_id IS NOT NULL THEN
            SELECT r.warehouse_id INTO v_from_wh
            FROM slots s JOIN tiers t ON s.tier_id=t.id
                         JOIN racks r ON t.rack_id=r.id
            WHERE s.id = NEW.from_slot_id;
          END IF;

          IF NEW.to_slot_id IS NOT NULL THEN
            SELECT r.warehouse_id INTO v_to_wh
            FROM slots s JOIN tiers t ON s.tier_id=t.id
                         JOIN racks r ON t.rack_id=r.id
            WHERE s.id = NEW.to_slot_id;
          END IF;

          IF NEW.from_slot_id IS NOT NULL THEN
            INSERT INTO stock_ledger(occurred_at, warehouse_id, slot_id, part_id, qty_change, movement_line_id)
            VALUES (NOW(), v_from_wh, NEW.from_slot_id, NEW.part_id, -NEW.qty, NEW.id);
          END IF;

          IF NEW.to_slot_id IS NOT NULL THEN
            INSERT INTO stock_ledger(occurred_at, warehouse_id, slot_id, part_id, qty_change, movement_line_id)
            VALUES (NOW(), v_to_wh, NEW.to_slot_id, NEW.part_id, NEW.qty, NEW.id);
          END IF;

          UPDATE storage_units
            SET warehouse_id = COALESCE(v_to_wh, warehouse_id),
                current_slot_id = NEW.to_slot_id
          WHERE id = NEW.storage_unit_id;

          RETURN NEW;
        END;
        $$ LANGUAGE plpgsql;

        DROP TRIGGER IF EXISTS trg_post_movement_line ON movement_lines;
        CREATE TRIGGER trg_post_movement_line
        AFTER INSERT ON movement_lines
        FOR EACH ROW
        EXECUTE FUNCTION fn_post_movement_line();
        ");
    }

    public function down(): void {
        DB::unprepared("DROP TRIGGER IF EXISTS trg_post_movement_line ON movement_lines;
                        DROP FUNCTION IF EXISTS fn_post_movement_line;");
        Schema::dropIfExists('stock_ledger');
    }
};
