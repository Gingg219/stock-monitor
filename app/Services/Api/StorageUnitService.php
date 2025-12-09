<?php

namespace App\Services\Api;

use App\Repositories\Contracts\StorageUnitsRepository;
use App\Services\Contracts\StorageUnitServiceInterface;

class StorageUnitService implements StorageUnitServiceInterface
{
    /**
     * @var StorageUnitsRepository
     */
    protected $repository;

    public function __construct(StorageUnitsRepository $repository)
    {
        $this->repository = $repository;
    }



    public function store($request) {
        
        return $request;
    }
}