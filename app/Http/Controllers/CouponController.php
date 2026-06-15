<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code'      => ['required', 'string'],
            'course_id' => ['required', 'exists:courses,id'],
            'amount'    => ['required', 'integer', 'min:0'],
        ]);

        $coupon = Coupon::where('code', strtoupper(trim($request->code)))->first();

        if (!$coupon) {
            return response()->json(['valid' => false, 'message' => 'کد تخفیف معتبر نیست.'], 422);
        }

        if (!$coupon->isValid()) {
            return response()->json(['valid' => false, 'message' => 'این کد تخفیف منقضی شده یا غیرفعال است.'], 422);
        }

        if (!$coupon->isValidForUser(Auth::id())) {
            return response()->json(['valid' => false, 'message' => 'شما قبلاً از این کد تخفیف استفاده کرده‌اید.'], 422);
        }

        if (!$coupon->isValidForAmount($request->amount)) {
            return response()->json([
                'valid'   => false,
                'message' => 'حداقل مبلغ سفارش برای این کد ' . price($coupon->min_order_amount) . ' است.',
            ], 422);
        }

        $discount = $coupon->calculateDiscount($request->amount);
        $finalAmount = $request->amount - $discount;

        return response()->json([
            'valid'         => true,
            'coupon_id'     => $coupon->id,
            'code'          => $coupon->code,
            'description'   => $coupon->description,
            'type_label'    => $coupon->type_label,
            'discount'      => $discount,
            'final_amount'  => $finalAmount,
            'message'       => 'کد تخفیف اعمال شد! ' . price($discount) . ' تخفیف گرفتی.',
        ]);
    }
}
