<?php

namespace App\Services\Api;

use App\Services\Contracts\WarehouseServiceInterface;
use App\Repositories\Contracts\WarehousesRepository;
use Illuminate\Support\Arr;

class WarehouseService implements WarehouseServiceInterface
{
    /**
     * @var WarehousesRepository
     */
    protected $repository;

    public function __construct(WarehousesRepository $repository)
    {
        $this->repository = $repository;
    }

    // public function index($request)
    // {
    //     return $this->repository->paginateByFilters(
    //         ['status' => @$request['status'], 'search' => @$request['search']],
    //         config('constants.DEFAULT_PAGINATION'),
    //         [],
    //         ['updated_at' => 'desc', 'category_cd' => 'desc']
    //     );
    // }

    

    public function index($filters = [])
    {
        $query = $this->repository
            ->with([
                'racks.tiers.slots', // load slot trước
            ]);

        if (!empty($filters['warehouse_id'])) {
            $query->where('id', $filters['warehouse_id']);
        }

        $warehouses = $query->get();

        // ----- phân loại theo mode -----
        $warehouses->each(function ($wh) {

            if ($wh->mode === 'fixed') {

                // --- KHO 2: load fixed_locations + parts ---
                $wh->load([
                    'racks.tiers.slots.fixed_locations.parts'
                ]);

                // trường hợp bạn muốn đảm bảo luôn có array
                $wh->racks->each(function ($rack) {
                    $rack->tiers->each(function ($tier) {
                        $tier->slots->each(function ($slot) {
                            if (!$slot->fixed_locations) {
                                $slot->fixed_locations = collect([]);
                            }
                        });
                    });
                });

            } else {

                // --- KHO 1: tính current_part từ storage_units ---
                $wh->load([
                    'racks.tiers.slots.currentStorageSummary.part'
                ]);

                // gán current_part cho từng slot
                $wh->racks->each(function ($rack) {
                    $rack->tiers->each(function ($tier) {
                        $tier->slots->each(function ($slot) {

                            $summary = $slot->currentStorageSummary->first();

                            if ($summary && $summary->part) {
                                $slot->current_part = $summary->part;
                                $slot->current_qty  = (float) $summary->qty;
                            } else {
                                $slot->current_part = null;
                                $slot->current_qty  = 0;
                            }

                            // xoá tránh trả summary thô ra FE
                            unset($slot->currentStorageSummary);
                        });
                    });
                });
            }
        });

        return $warehouses;
    }


    public function store($request) {
        // $slot = $this->where(['name' => $request['name'], '' => $request['book_cd']])->first();

        // if (!empty($slot)) {
        //     $slot->update([
        //         //...
        //     ]);
        // } else {
        //     $uploadFile = $this->upload($request['image'], 'slots');
        //     $request['image'] = $uploadFile;
        //     $slot = $this->create($request);
        // }
        // $slot->users()->attach([$request['user_id'] => ['quantity' => $request['quantity']]]);
        // $slot->categories()->sync($request['categories']);
        // return $slot;
        return $request;
    }

    // public function updateWithRelations($id, array $data)
    // {
    //     $warehouse = $this->find($id);

    //     // 1. Update bản thân warehouse
    //     $warehouse->update(Arr::only($data, [
    //         'name',
    //         'code',
    //         'description',
    //     ]));

    //     // 2. Xử lý racks
    //     $rackIds = [];

    //     if (!empty($data['racks']) && is_array($data['racks'])) {
    //         foreach ($data['racks'] as $rackData) {
    //             $rack = null;

    //             if (!empty($rackData['id'])) {
    //                 $rack = $warehouse->racks()->find($rackData['id']);
    //             }

    //             if (!$rack) {
    //                 // tạo mới, gắn qua quan hệ để tự set warehouse_id
    //                 $rack = $warehouse->racks()->make();
    //             }

    //             $rack->fill(Arr::only($rackData, ['name', 'code', 'sort_order']));
    //             $rack->save();

    //             $rackIds[] = $rack->id;

    //             // 3. Xử lý tiers trong từng rack
    //             $tierIds = [];
    //             if (!empty($rackData['tiers']) && is_array($rackData['tiers'])) {
    //                 foreach ($rackData['tiers'] as $tierData) {
    //                     $tier = null;

    //                     if (!empty($tierData['id'])) {
    //                         $tier = $rack->tiers()->find($tierData['id']);
    //                     }

    //                     if (!$tier) {
    //                         $tier = $rack->tiers()->make();
    //                     }

    //                     $tier->fill(Arr::only($tierData, ['name', 'code', 'sort_order']));
    //                     $tier->save();

    //                     $tierIds[] = $tier->id;

    //                     // 4. Xử lý slots trong từng tier
    //                     $slotIds = [];
    //                     if (!empty($tierData['slots']) && is_array($tierData['slots'])) {
    //                         foreach ($tierData['slots'] as $slotData) {
    //                             $slot = null;

    //                             if (!empty($slotData['id'])) {
    //                                 $slot = $tier->slots()->find($slotData['id']);
    //                             }

    //                             if (!$slot) {
    //                                 $slot = $tier->slots()->make();
    //                             }

    //                             $slot->fill(Arr::only($slotData, ['name', 'code', 'sort_order']));
    //                             $slot->save();

    //                             $slotIds[] = $slot->id;

    //                             // 5. fixed_location (1-1 với slot)
    //                             if (!empty($slotData['fixed_location'])) {
    //                                 $flData = $slotData['fixed_location'];

    //                                 $fixedLocation = $slot->fixed_location; // hasOne
    //                                 if (!$fixedLocation) {
    //                                     $fixedLocation = $slot->fixed_location()->make();
    //                                 }

    //                                 $fixedLocation->fill(Arr::only($flData, [
    //                                     'max_qty',
    //                                     'min_qty',
    //                                     'part_id',
    //                                 ]));
    //                                 $fixedLocation->save();
    //                             }
    //                         }
    //                     }

    //                     // (tùy chọn) xóa slots bị remove trên form
    //                     if (!empty($slotIds)) {
    //                         $tier->slots()->whereNotIn('id', $slotIds)->delete();
    //                     }
    //                 }
    //             }

    //             // (tùy chọn) xóa tiers bị remove trên form
    //             if (!empty($tierIds)) {
    //                 $rack->tiers()->whereNotIn('id', $tierIds)->delete();
    //             }
    //         }
    //     }

    //     // (tùy chọn) xóa racks bị remove trên form
    //     if (!empty($rackIds)) {
    //         $warehouse->racks()->whereNotIn('id', $rackIds)->delete();
    //     }

    //     return $warehouse->load([
    //         'racks.tiers.slots.fixed_locations.parts'
    //     ]);
    // }

    // public function destroy($id) {
    //     if (count($this->firstById($id, 'slots')->slots) != 0) return NULL;
    //     return $this->delete($id);
    // }
}