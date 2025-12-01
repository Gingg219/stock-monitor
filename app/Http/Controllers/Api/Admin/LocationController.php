<?php

namespace App\Http\Controllers\Api\Admin;

use App\Repositories\Contracts\RacksRepository;
use App\Repositories\Contracts\SlotsRepository;
use App\Repositories\Contracts\TiersRepository;
use App\Repositories\Contracts\WarehousesRepository;
use App\Http\Controllers\Controller;
use App\Services\Contracts\WarehouseServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class LocationController extends Controller
{
    protected $WarehouseService;

    public function __construct(
        WarehouseServiceInterface $WarehouseService
    ) 
    {
        $this->WarehouseService = $WarehouseService;
        $this->authorize('is-operator');
    }

    public function index(): JsonResponse
    {
        $warehouses = $this->WarehouseService->index();
        return response()->json($warehouses);
    }

    // public function store($request)
    // {
    //     $slots = $this->warehouseRepo->store($request);
    //     if (!$slots) {
    //         return response()->json(['status' => '400', 'message' => 'Thêm mới thất bại!']);
    //     } else {
    //         // return response()->json(['status' => '200', 'message' => 'Thêm mới thành công!']);
    //     }
    //     $data = $request->toArray();

    //     return response()->json([
    //         'status' => '200',
    //         'message' => 'Thêm mới thành công!',
    //         'data' => $data // Thêm dữ liệu vào phản hồi
    //     ], 200);
    // }

    // public function update($id,$request)
    // {
    //     $slots = $this->warehouseRepo->updateWithRelations($id,$request);
    //     if (!$slots) return response()->json(['status' => '400', 'message' => 'Thêm mới thất bại!']);

    //     return response()->json([
    //         'status' => '200',
    //         'message' => 'Thêm mới thành công!',
    //         'data' => $slots // Thêm dữ liệu vào phản hồi
    //     ], 200);
    // }

    // public function destroy()
    // {
    //     $warehouses = $this->warehouseRepo->getWarehouseTree();
    //     return response()->json($warehouses);
    // }
}