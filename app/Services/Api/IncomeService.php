<?php

namespace App\Services\Api;

use App\Repositories\Contracts\IncomeLinesRepository;
use App\Repositories\Contracts\IncomesRepository;
use App\Repositories\Contracts\PartsRepository;
use App\Repositories\Contracts\VendorsRepository;
use App\Services\Contracts\IncomeServiceInterface;
use App\Traits\FileTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class IncomeService implements IncomeServiceInterface
{
    // use FileTrait;
    protected $repo;
    protected $lineRepo;
    protected $partRepo;
    protected $vendorRepo;

    public function __construct(
        IncomesRepository $repository,
        IncomeLinesRepository $incomeLinesRepository,
        PartsRepository $partRepository,
        VendorsRepository $vendorRepository
    ) {
        $this->repo = $repository;
        $this->lineRepo = $incomeLinesRepository;
        $this->partRepo = $partRepository;
        $this->vendorRepo = $vendorRepository;
    }

    // Lấy danh sách Incomes với phân trang và lọc
    public function  index($request)
    {
     return $this->repo->paginateByFilters(
            [],
            15,
            [
                'income_lines:id,part_id,vendor_id,income_id,qty_total,lot_no,expiry_date,remark',
                'income_lines.parts:id,part_no,name,snp,has_expiry,expiry_days',
                'income_lines.vendors:id,code,name',
            ],
            ['created_at' => 'desc']
        );
    }

    public function  show($id)
    {
     return $this->repo->find($id);
    }

    /**
     * Tạo hoặc cập nhật Income + Lines
     */
    public function store(array $request)
    {
        $createdBy = Auth::id();
        $incomeData = [
            'income_no'   => $request['income_no'] ?? null,
            'invoice_no'  => $request['invoice_no'] ?? null,
            'received_at' => $request['received_at'] ?? now(),
            'note'        => $request['note'] ?? null,
            'created_by'  => $createdBy,
        ];

        // Debug thử
        Log::info('Income request:', $incomeData);

        
        // 1. Tìm income theo invoice

        if ($request['id'] != null) {
            $income = $this->repo->find($request['id']);
            $income->update($incomeData);
        } else {
            $income = $this->repo->create($incomeData);
        }

        // Debug
        Log::info('Income saved:', $income->toArray());

        // 3. Xử lý income lines
        if (!empty($request['lines']) && is_array($request['lines'])) {

            $existingLineIds = []; // các ID dòng được giữ lại (cũ hoặc mới)

            foreach ($request['lines'] as $line) {
                // tìm part bằng part_no (hoặc line gửi lên có part_id thì dùng trực tiếp)
                $partId = null;
                if (!empty($line['part_no'])) {
                    $part = $this->partRepo->findWhere(['part_no' => $line['part_no']])->first();
                    $partId = $part->id;
                }

                // tìm vendor bằng code hoặc vendor_id
                $vendorId = null;
                if (!empty($line['vendor_code'])) {
                    $vendor = $this->vendorRepo->findWhere(['code' => $line['vendor_code']])->first();
                    $vendorId = $vendor->id;
                }

                // Validate bắt buộc: partId & qty
                if (empty($partId)) {
                    // bạn có thể throw exception hoặc ghi log và tiếp tục tuỳ nhu cầu debug
                    Log::warning("Part not found for line: " . json_encode($line));
                    continue; // hoặc throw new \Exception("Part not found");
                }

                $lineData = [
                    'income_id'     => $income->id,
                    'part_id'       => $partId,
                    'vendor_id'     => $vendorId,
                    'lot_no'        => $line['lot_no'] ?? null,
                    'expiry_date'   => $line['expiry_date'] ?? null,
                    'qty_total'     => $line['qty_total'] ?? 0,
                    'snp_overwrite' => $line['snp_overwrite'] ?? null,
                    'remark'        => $line['remark'] ?? null,
                ];

                $savedLine = null;

                // Update nếu có id (và id thuộc về income này)
                if (($line['id']) != null) {
                    // optional: kiểm tra line thuộc income này để tránh update chéo
                    $existing = $this->lineRepo->find($line['id']);
                    if ($existing && $existing->income_id == $income->id) {
                        $savedLine = $this->lineRepo->update($lineData, $line['id']);
                        $existingLineIds[] = $savedLine->id;
                    } else {
                        Log::warning("Line id {$line['id']} not found or not belong to income {$income->id}");
                        // Bạn có thể choose create mới hoặc skip
                        $savedLine = $this->lineRepo->create($lineData);
                        $existingLineIds[] = $savedLine->id;
                    }
                } else {
                    // Create mới
                    $savedLine = $this->lineRepo->create($lineData);
                    // **RẤT QUAN TRỌNG**: thêm ID của dòng mới vào existingLineIds để không xóa nó sau này
                    $existingLineIds[] = $savedLine->id;
                }
            }

            // Lấy tất cả ID lines hiện có của income
            $allExistingLineIds = $this->lineRepo->findWhere(['income_id' => $income->id])->pluck('id')->toArray();

            $idsToDelete = array_diff($allExistingLineIds, $existingLineIds);

            if (!empty($idsToDelete)) {
                Log::info("Deleting lines for income {$income->id}: " . implode(', ', $idsToDelete));

                // Nếu repo có method deleteWhereIn, dùng nó; nếu không, xoá từng cái
                if (method_exists($this->lineRepo, 'deleteWhereIn')) {
                    $this->lineRepo->deleteWhereIn('id', $idsToDelete);
                } else {
                    // fallback: dùng model underlying (nếu repo expose getModel)
                    if (method_exists($this->lineRepo, 'getModel')) {
                        $model = $this->lineRepo->getModel();
                        $model->whereIn('id', $idsToDelete)->delete();
                    } else {
                        // fallback cuối cùng: lặp và delete từng dòng
                        foreach ($idsToDelete as $delId) {
                            try {
                                $this->lineRepo->delete($delId);
                            } catch (\Exception $e) {
                                Log::error("Error deleting line {$delId}: " . $e->getMessage());
                            }
                        }
                    }
                }
            }

        } else {
            // Nếu client gửi lines rỗng => xóa hết lines cho income (nếu đó là ý định)
            Log::info("Deleting all lines for income ID {$income->id} because request lines empty");
            $this->lineRepo->deleteWhere(['income_id' => $income->id]);
        }

        return $income->load('income_lines');
    }
}