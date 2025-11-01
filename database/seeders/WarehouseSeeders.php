<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WarehouseSeeders extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Warehouses
            $k1 = DB::table('warehouses')->insertGetId([
                'code' => 'K1','name' => 'Kho 1','mode' => 'non_fixed','is_active'=>true,'created_at'=>now(),'updated_at'=>now()
            ]);
            $k2 = DB::table('warehouses')->insertGetId([
                'code' => 'K2','name' => 'Kho 2','mode' => 'fixed','is_active'=>true,'created_at'=>now(),'updated_at'=>now()
            ]);

            // Racks A,B,C cho mỗi kho
            foreach ([$k1,$k2] as $whId) {
                foreach (['A','B','C'] as $rackCode) {
                    $rackId = DB::table('racks')->insertGetId([
                        'warehouse_id'=>$whId,'code'=>$rackCode
                    ]);
                    // Tiers 1..3
                    for ($tier=1;$tier<=3;$tier++) {
                        $tierId = DB::table('tiers')->insertGetId([
                            'rack_id'=>$rackId,'level_no'=>$tier
                        ]);
                        // Slots: 1..10
                        for ($i=1;$i<=10;$i++) {
                            DB::table('slots')->insert([
                                'tier_id'=>$tierId,
                                'code'=>sprintf('%s-%d', $rackCode, $i),
                                'allowed_unit'=> $whId===$k1
                                    ? ($tier===1 ? 'box' : 'pallet') // Kho1: T1 box, T2/T3 pallet
                                    : 'any',
                                'is_active'=>true,
                            ]);
                        }
                    }
                }
            }

            // Vendor & Parts mẫu
            $vnd = DB::table('vendors')->insertGetId(['code'=>'VND1','name'=>'Default Vendor']);
            $p1 = DB::table('parts')->insertGetId([
                'part_no'=>'QK2-0001-000','name'=>'Part 0001','vendor_id'=>$vnd,'snp'=>4000,'has_expiry'=>true,'expiry_days'=>365,'created_at'=>now(),'updated_at'=>now()
            ]);
            $p2 = DB::table('parts')->insertGetId([
                'part_no'=>'QK2-0002-000','name'=>'Part 0002','vendor_id'=>$vnd,'snp'=>4000,'has_expiry'=>true,'expiry_days'=>365,'created_at'=>now(),'updated_at'=>now()
            ]);

            // Map cố định cho Kho 2: Part -> một slot (ví dụ Rack A, Tầng 1, Slot 1)
            $k2RackA = DB::table('racks')->where('warehouse_id',$k2)->where('code','A')->first();
            $k2Tier1A = DB::table('tiers')->where('rack_id',$k2RackA->id)->where('level_no',1)->first();
            $k2SlotA1 = DB::table('slots')->where('tier_id',$k2Tier1A->id)->where('code','A-1')->first();

            DB::table('fixed_locations')->insert([
                'warehouse_id'=>$k2, 'part_id'=>$p1, 'slot_id'=>$k2SlotA1->id, 'min_qty'=>4000, 'max_qty'=>20000
            ]);

            // Tài khoản app_users demo
            DB::table('app_users')->insert([
                'username'=>'admin','full_name'=>'Admin','is_active'=>true,'created_at'=>now(),'updated_at'=>now()
            ]);
        });
    }
}
