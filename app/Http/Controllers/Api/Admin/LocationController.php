<?php

namespace App\Http\Controllers\Api\Admin;

use App\Repositories\Contracts\RacksRepository;
use App\Repositories\Contracts\SlotsRepository;
use App\Repositories\Contracts\TiersRepository;
use App\Repositories\Contracts\WarehousesRepository;
use App\Http\Controllers\Controller;
use App\Services\Contracts\LocationServiceInterface;
use App\Services\Contracts\WarehouseServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class LocationController extends Controller
{
    protected $WarehouseService;
    protected $service;

    public function __construct(
        WarehouseServiceInterface $WarehouseService,
        LocationServiceInterface $service
    ) 
    {
        $this->WarehouseService = $WarehouseService;
        $this->service = $service;
        $this->authorize('is-operator');
    }

    public function index(): JsonResponse
    {
        $warehouses = $this->WarehouseService->index();
        return response()->json($warehouses);
    }

    public function warehouses(Request $request)
    {
        return response()->json($this->service->warehouses($request));
    }

    public function racks(Request $request)
    {
        return response()->json($this->service->racks($request));
    }

    public function tiers(Request $request)
    {
        return response()->json($this->service->tiers($request));
    }

    public function slots(Request $request)
    {
        return response()->json($this->service->slots($request));
    }

}