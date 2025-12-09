<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Contracts\StorageUnitServiceInterface;
use Illuminate\Http\Request;

class StorageUnitController extends Controller
{
    /**
     * Parameter
     *
     * @var StorageUnitServiceInterface
     */
    protected $storageUnitService;

    /**
     * UserServiceInterface constructor.
     *
     * @param IncomeServiceInterface $incomeService
     */
    public function __construct(
        StorageUnitServiceInterface $storageUnitService,
    )
    {
        $this->storageUnitService = $storageUnitService;
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $income = $this->storageUnitService->store($data);

        return response()->json([
            'success' => true,
            'data' => $income,
        ]);
    }

}
