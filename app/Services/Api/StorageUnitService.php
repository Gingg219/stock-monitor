<?php

namespace App\Services\Api;

use App\Repositories\Contracts\IncomeLinesRepository;
use App\Repositories\Contracts\IncomesRepository;
use App\Repositories\Contracts\PartsRepository;
use App\Repositories\Contracts\SlotsRepository;
use App\Repositories\Contracts\StorageUnitsRepository;
use App\Services\Contracts\StorageUnitServiceInterface;
use Illuminate\Support\Facades\DB;

class StorageUnitService implements StorageUnitServiceInterface
{
    /**
     * @var StorageUnitsRepository
     */
    protected $repo;
    protected $partRepo;
    protected $incomeLineRepo;
    protected $incomeRepo;
    protected $slotRepo;

    public function __construct(
        StorageUnitsRepository $repo,
        PartsRepository $partRepo,
        IncomeLinesRepository $incomeLineRepo,
        IncomesRepository $incomeRepo,
        SlotsRepository $slotRepo,

    )
    {
        $this->repo = $repo;
        $this->partRepo = $partRepo;
        $this->incomeLineRepo = $incomeLineRepo;
        $this->incomeRepo = $incomeRepo;
        $this->slotRepo = $slotRepo;
    }

    public function store($request)
    {
        $income = $this->incomeRepo->find($request['incomeId']);
        if (!$income) {
            return response()->json([
                'success' => false,
                'message' => "Income id {$request['incomeId']} không tồn tại"
            ], 404);
        }

        $labels = $request['labels'];

        $created = [];
        foreach ($labels as $lab) {
            // determine part_id
            $partId = null;
            if (!empty($lab['part_no'])) {
                $part = $this->partRepo->findWhere(['part_no' => $lab['part_no']])->first();
                if ($part) $partId = $part->id;
            }

            // try resolve income_line_id:
            $incomeLineId = null;
            if (!empty($lab['lineNo'])) {
                // if backend's lineNo is indeed income_lines.id, prefer direct find
                $il = $this->incomeLineRepo->find($lab['lineNo']);
                if ($il && $il->income_id == $income->id) {
                    $incomeLineId = $il->id;
                    if (!$partId && $il->part_id) $partId = $il->part_id;
                }
            }

            // fallback: try find income_line by matching part_id+lot_no+income_id
            if (!$incomeLineId && $partId && !empty($lab['lot_no'])) {
                $il = $this->incomeLineRepo->findWhere(['income_id'=> $income->id])
                    ->where('part_id', $partId)
                    ->where('lot_no', $lab['lot_no'])
                    ->first();
                if ($il) $incomeLineId = $il->id;
            }

            // if still no part_id, error because part is required for storage unit
            if (!$partId) {
                return response()->json([
                    'success' => false,
                    'message' => "Part not found for label code {$lab['code']} (part_no: {$lab['part_no']})"
                ], 422);
            }

            // map fields to storage_units columns
            $suData = [
                'unit_type' => strtolower($lab['type']),
                'unit_code' => $lab['code'],
                'income_line_id' => $incomeLineId,
                'part_id' => $partId,
                'lot_no' => $lab['lot_no'] ?? null,
                'expiry_date' => !empty($lab['expiry']) ? date('Y-m-d', strtotime($lab['expiry'])) : null,
                'qty' => $lab['qty'] ?? 0,
                'warehouse_id' =>  null,
                'current_slot_id' =>  null,
            ];

            // create storage unit
            $su = $this->repo->create($suData);
            $created[] = $su;
        }
        return $created;
    }

    function getLatestCode($incomeId) {

        $results = DB::table('storage_units')
        ->select([
            'storage_units.part_id',
            'storage_units.unit_type',
            'parts.part_no',
            // Các hàm tổng hợp
            DB::raw('SUM(storage_units.qty) as total_qty'),
            DB::raw('MAX(storage_units.sequence) as sequence_latest'),

            // Lấy thông tin từ income_lines, những trường này phải có trong GROUP BY
            'income_lines.income_id',
        ])
        // Tham gia với bảng income_lines để có thể lọc theo income_id
        ->join('income_lines', 'storage_units.income_line_id', '=', 'income_lines.id')
        ->join('parts', 'income_lines.part_id', '=', 'parts.id')
        // Lọc theo income_id cụ thể
        ->where('income_lines.income_id', $incomeId)
        // Nhóm theo các cột không tổng hợp
        ->groupBy([
            'storage_units.part_id',
            'storage_units.unit_type',
            'parts.part_no',
            'income_lines.income_id',
        ])
        ->get();

        return $results;
    }
}