<?php

namespace App\Services\Contracts;

interface StorageUnitServiceInterface
{
    public function store($request);
    public function getLatestCode($incomeLineId);public function assignLocation(array $data);
    public function scanPutAway(array $data);
    public function changeLocation(array $data);
    public function getAllByStatus(array $request);
}