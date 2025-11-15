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
        $warehouses = $this->warehouseRepo->getWarehouseTree();
        return response()->json($warehouses);
    }
}
