<?php

namespace App\Services\Api;

use App\Repositories\Contracts\IncomeLinesRepository;
use App\Repositories\Contracts\IncomesRepository;
use App\Repositories\Contracts\PartsRepository;
use App\Repositories\Contracts\RacksRepository;
use App\Repositories\Contracts\SlotsRepository;
use App\Repositories\Contracts\TiersRepository;
use App\Repositories\Contracts\VendorsRepository;
use App\Repositories\Contracts\WarehousesRepository;
use App\Services\Contracts\LocationServiceInterface;

class LocationService implements LocationServiceInterface
{
    // use FileTrait;
    protected $warehouseRepo;
    protected $rackRepo;
    protected $tierRepo;
    protected $slotRepo;

    public function __construct(
        WarehousesRepository $repository,
        RacksRepository $rackRepository,
        TiersRepository $tierRepository,
        SlotsRepository $slotRepository
    ) {
        $this->warehouseRepo = $repository;
        $this->rackRepo = $rackRepository;
        $this->tierRepo = $tierRepository;
        $this->slotRepo = $slotRepository;
    }

    public function warehouses($request)
    {
        return $this->warehouseRepo->getAllByFilters(
            ['search' => @$request->search],
            [],
            ['code' => 'asc'],
            ['id', 'code']
        );
    }

    public function racks($request)
    {
        return $this->rackRepo->getAllByFilters(
            [
                'warehouse_id' => $request->warehouse_id,
                'search' => $request->search
            ],
            [],
            ['code' => 'asc'],
            ['id', 'code']
        );
    }

    public function tiers($request)
    {
        return $this->tierRepo->getAllByFilters(
            [
                'rack_id' => $request->rack_id,
                'search' => $request->search
            ],
            [],
            ['level_no' => 'asc'],
            ['id', 'level_no']
        );
    }

    public function slots($request)
    {
        return $this->slotRepo->getAllByFilters(
            [
                'tier_id' => $request->tier_id,
                'search' => $request->search
            ],
            [],
            ['code' => 'asc'],
            ['id', 'code']
        );
    }
    
}