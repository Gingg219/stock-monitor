<?php

namespace App\Http\Controllers\Api\Admin;

use App\Contracts\Repositories\RacksRepository;
use App\Contracts\Repositories\SlotsRepository;
use App\Contracts\Repositories\TiersRepository;
use App\Contracts\Repositories\WarehousesRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class LocationController extends Controller
{
    public function __construct(
    protected WarehousesRepository $warehouseRepo,
    protected RacksRepository $rackRepo,
    protected TiersRepository $tierRepo,
    protected SlotsRepository $slotRepo,
    ) 
    {
        $this->authorize('is-operator');
    }

    public function index()
    {
        $warehouses = $this->warehouseRepo->index();
        return response()->json($warehouses);
    }

    public function store($request)
    {
        $slots = $this->warehouseRepo->store($request);
        if (!$slots) {
            return response()->json(['status' => '400', 'message' => 'Thêm mới thất bại!']);
        } else {
            // return response()->json(['status' => '200', 'message' => 'Thêm mới thành công!']);
        }
        $data = $request->toArray();

        return response()->json([
            'status' => '200',
            'message' => 'Thêm mới thành công!',
            'data' => $data // Thêm dữ liệu vào phản hồi
        ], 200);
    }

    public function update($request,$id)
    {
        $slots = $this->warehouseRepo->store($request, $id);
        if (!$slots) return response()->json(['status' => '400', 'message' => 'Thêm mới thất bại!']);

        return response()->json([
            'status' => '200',
            'message' => 'Thêm mới thành công!',
            'data' => $slots // Thêm dữ liệu vào phản hồi
        ], 200);
    }

    public function destroy()
    {
        $warehouses = $this->warehouseRepo->getWarehouseTree();
        return response()->json($warehouses);
    }
}