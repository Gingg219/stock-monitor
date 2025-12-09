<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Parts;
use App\Models\Vendors;

class StoreIncomeRequest extends FormRequest
{
    public function authorize()
    {
        return true; // cho phép luôn
    }

    public function rules()
    {
        return [
            'id' => 'nullable|integer',
            'warehouse_id' => 'required|integer|exists:warehouses,id',
            'income_no' => 'required|string|max:255',
            'invoice_no' => 'nullable|string|max:255',
            'received_at' => 'required|date',

            'lines' => 'required|array|min:1',
            'lines.*.id' => 'nullable|integer',
            'lines.*.part_no' => 'required|string|max:255',
            'lines.*.lot_no' => 'required|string|max:255',
            'lines.*.vendor_code' => 'required|string|max:255',
            'lines.*.qty_total' => 'required|numeric|min:0.0001',
            'lines.*.expiry_date' => 'nullable|date',
            'lines.*.remark' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'warehouse_id.required' => 'Bạn chưa chọn kho',
            'income_no.required' => 'Số chứng từ không được để trống',
            'received_at.required' => 'Ngày chứng từ không được để trống',
            'lines.required' => 'Phải có ít nhất một dòng hàng',
            'lines.*.part_no.required' => 'Mã part không được để trống',
            'lines.*.vendor_code.required' => 'Mã vendor không được để trống',
            'lines.*.qty_total.required' => 'Số lượng không được để trống',
        ];
    }

    // ⭐⭐⭐ Custom validate part/vendor tồn tại
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $lines = $this->input('lines', []);

            foreach ($lines as $index => $line) {

                // Check Part
                $part = Parts::where('part_no', $line['part_no'])->first();
                if (!$part) {
                    $validator->errors()->add(
                        "lines.$index.part_no",
                        "Part '{$line['part_no']}' không tồn tại"
                    );
                }

                // Check Vendor
                $vendor = Vendors::where('code', $line['vendor_code'])->first();
                if (!$vendor) {
                    $validator->errors()->add(
                        "lines.$index.vendor_code",
                        "Vendor '{$line['vendor_code']}' không tồn tại"
                    );
                }
            }
        });
    }
}
