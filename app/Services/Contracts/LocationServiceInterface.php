<?php

namespace App\Services\Contracts;

interface LocationServiceInterface
{
    public function warehouses($request);
    public function racks($request);
    public function tiers($request);
    public function slots($request);
}