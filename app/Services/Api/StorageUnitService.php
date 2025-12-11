<?php

namespace App\Services\Api;

use App\Repositories\Contracts\IncomeLinesRepository;
use App\Repositories\Contracts\IncomesRepository;
use App\Repositories\Contracts\PartsRepository;
use App\Repositories\Contracts\SlotsRepository;
use App\Repositories\Contracts\StorageUnitsRepository;
use App\Services\Contracts\StorageUnitServiceInterface;

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
                'unit_type' => strtoupper($lab['type']),
                'unit_code' => $lab['code'],
                'income_line_id' => $incomeLineId,
                'part_id' => $partId,
                'lot_no' => $lab['lot_no'] ?? null,
                'expiry_date' => !empty($lab['expiry']) ? date('Y-m-d', strtotime($lab['expiry'])) : null,
                'qty' => $lab['qty'] ?? 0,
                'warehouse_id' => $income->warehouse_id ?? ($request->input('warehouse_id') ?? null),
                'current_slot_id' => $request->input('current_slot_id') ?? null,
            ];

            // create storage unit
            $su = $this->repo->create($suData);
            $created[] = $su;
        }
        return $created;
    }
}