<?php

namespace App\Services\Contracts;

interface StorageUnitServiceInterface
{
    public function store($request);
    public function getLatestCode($incomeLineId);
}