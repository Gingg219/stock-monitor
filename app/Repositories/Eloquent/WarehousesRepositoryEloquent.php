<?php

namespace App\Repositories\Eloquent;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Contracts\Repositories\WarehousesRepository;
use App\Models\Warehouses;
use App\Validators\WarehousesValidator;

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

    public function update($request, $id) {
        // $slot = $this->where(['name' => $request['name'], 'book_cd' => $request['book_cd']])->first();

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
    }

    public function destroy($id) {
        if (count($this->firstById($id, 'slots')->slots) != 0) return NULL;
        return $this->delete($id);
    }
}
