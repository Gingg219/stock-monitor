<?php

namespace App\Services\Contracts;

interface IncomeServiceInterface
{
    public function index(array $request);
    public function show(array $id);
    public function store(array $request);
}