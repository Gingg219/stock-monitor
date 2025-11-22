<?php

namespace App\Repositories\Eloquent;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Contracts\Repositories\WarehousesRepository;
use App\Models\Warehouses;
use App\Validators\WarehousesValidator;
use Illuminate\Support\Arr;

/**
 * Class WarehousesRepositoryEloquent.
 *
 * @package namespace App\Repositories\Eloquent;
 */
class WarehousesRepositoryEloquent extends BaseRepository implements WarehousesRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Warehouses::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function index($filters = [])
    {
        $query = $this->model->with([
            'racks.tiers.slots.fixed_locations.parts'
        ]);

        if (!empty($filters['warehouse_id'])) {
            $query->where('id', $filters['warehouse_id']);
        }

        // dd($query->toSql());

        return $query->get();
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

    public function updateWithRelations($id, array $data)
    {
        $warehouse = $this->find($id);

        // 1. Update bản thân warehouse
        $warehouse->update(Arr::only($data, [
            'name',
            'code',
            'description',
        ]));

        // 2. Xử lý racks
        $rackIds = [];

        if (!empty($data['racks']) && is_array($data['racks'])) {
            foreach ($data['racks'] as $rackData) {
                $rack = null;

                if (!empty($rackData['id'])) {
                    $rack = $warehouse->racks()->find($rackData['id']);
                }

                if (!$rack) {
                    // tạo mới, gắn qua quan hệ để tự set warehouse_id
                    $rack = $warehouse->racks()->make();
                }

                $rack->fill(Arr::only($rackData, ['name', 'code', 'sort_order']));
                $rack->save();

                $rackIds[] = $rack->id;

                // 3. Xử lý tiers trong từng rack
                $tierIds = [];
                if (!empty($rackData['tiers']) && is_array($rackData['tiers'])) {
                    foreach ($rackData['tiers'] as $tierData) {
                        $tier = null;

                        if (!empty($tierData['id'])) {
                            $tier = $rack->tiers()->find($tierData['id']);
                        }

                        if (!$tier) {
                            $tier = $rack->tiers()->make();
                        }

                        $tier->fill(Arr::only($tierData, ['name', 'code', 'sort_order']));
                        $tier->save();

                        $tierIds[] = $tier->id;

                        // 4. Xử lý slots trong từng tier
                        $slotIds = [];
                        if (!empty($tierData['slots']) && is_array($tierData['slots'])) {
                            foreach ($tierData['slots'] as $slotData) {
                                $slot = null;

                                if (!empty($slotData['id'])) {
                                    $slot = $tier->slots()->find($slotData['id']);
                                }

                                if (!$slot) {
                                    $slot = $tier->slots()->make();
                                }

                                $slot->fill(Arr::only($slotData, ['name', 'code', 'sort_order']));
                                $slot->save();

                                $slotIds[] = $slot->id;

                                // 5. fixed_location (1-1 với slot)
                                if (!empty($slotData['fixed_location'])) {
                                    $flData = $slotData['fixed_location'];

                                    $fixedLocation = $slot->fixed_location; // hasOne
                                    if (!$fixedLocation) {
                                        $fixedLocation = $slot->fixed_location()->make();
                                    }

                                    $fixedLocation->fill(Arr::only($flData, [
                                        'max_qty',
                                        'min_qty',
                                        'part_id',
                                    ]));
                                    $fixedLocation->save();
                                }
                            }
                        }

                        // (tùy chọn) xóa slots bị remove trên form
                        if (!empty($slotIds)) {
                            $tier->slots()->whereNotIn('id', $slotIds)->delete();
                        }
                    }
                }

                // (tùy chọn) xóa tiers bị remove trên form
                if (!empty($tierIds)) {
                    $rack->tiers()->whereNotIn('id', $tierIds)->delete();
                }
            }
        }

        // (tùy chọn) xóa racks bị remove trên form
        if (!empty($rackIds)) {
            $warehouse->racks()->whereNotIn('id', $rackIds)->delete();
        }

        return $warehouse->load([
            'racks.tiers.slots.fixed_locations.parts'
        ]);
    }

    public function destroy($id) {
        if (count($this->firstById($id, 'slots')->slots) != 0) return NULL;
        return $this->delete($id);
    }
}
