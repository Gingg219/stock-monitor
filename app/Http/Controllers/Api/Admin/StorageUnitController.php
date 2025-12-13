<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Contracts\StorageUnitServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
        $payload = $request->all();

        // Validate request shape
        $validator = Validator::make($payload, [
            'incomeId' => 'nullable|integer',
            'income_no' => 'nullable|string',
            // 'labels' => 'required|array|min:1',
            'labels.*.code' => 'required|string',
            'labels.*.type' => 'required|string',
            'labels.*.qty' => 'required|numeric|min:0',
            'labels.*.part_no' => 'nullable|string',
            'labels.*.lot_no' => 'nullable|string',
            'labels.*.expiry' => 'nullable|date',
            'labels.*.lineNo' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors(),
            ], 422);
        }

        $income = $this->storageUnitService->store($payload);

        return response()->json([
            'success' => true,
            'data' => $income,
        ]);
    }

    public function getLatestCode($incomeId)
    {
        $data = $this->storageUnitService->getLatestCode($incomeId);

        return response()->json([
        'success' => true,
        'data' => $data,
        ]);

    }

}
