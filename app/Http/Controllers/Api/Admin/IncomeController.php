<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIncomeRequest;
use App\Services\Contracts\IncomeServiceInterface;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    /**
     * Parameter
     *
     * @var IncomeServiceInterface
     */
    protected $incomeService;

    /**
     * UserServiceInterface constructor.
     *
     * @param IncomeServiceInterface $incomeService
     */
    public function __construct(
        IncomeServiceInterface $incomeService,
    )
    {
        $this->incomeService = $incomeService;
    }

    public function index(Request $request)
    {
        $data = $request->all();
        $income = $this->incomeService->index( $data);
        return response()->json([
            'success' => true,
            'data' => $income,
        ]);
    }

    public function show($id)
    {
        $income = $this->incomeService->show($id);
        if (!$income) {
            return response()->json(['message' => 'Income not found'], 404);
        }
        return response()->json($income);
    }

    public function store(StoreIncomeRequest $request)
    {
        $data = $request->validated();

        // Map part_no & vendor_code sang ID để service xử lý
        foreach ($data['lines'] as &$line) {
            $line['part_id'] = \App\Models\Parts::where('part_no', $line['part_no'])->value('id');
            $line['vendor_id'] = \App\Models\Vendors::where('code', $line['vendor_code'])->value('id');
        }

        $income = $this->incomeService->store($data);

        return response()->json([
            'success' => true,
            'data' => $income,
        ]);
    }

}
