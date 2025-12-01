<?php

namespace App\Services\Contracts;

interface WarehouseServiceInterface
{
    public function index($filters = []);
}