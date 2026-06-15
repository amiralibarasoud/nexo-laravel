<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\Payment\ZarinpalService;
use App\Services\Payment\ZibalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(
        private ZarinpalService $zarinpal,
        private ZibalService $zibal,
    ) {}

    public function checkout(Request $request, Course $course): Response|RedirectResponse
    {
        if (!Auth::check()) {
            session(['url.intended' => route('courses.show', $course->slug)]);
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->isEnrolledIn($course)) {
            return redirect()->route('courses.learn', $course->slug)
                ->with('info', 'شما قبلاً در این دوره ثبت‌نام کرده‌اید.');
        }

        return Inertia::render('Payment/Checkout', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'cover_image' => $course->cover_image,
                'price' => $course->price,
                'discounted_price' => $course->discounted_price,
                'effective_price' => $course->effective_price,
                'is_discounted' => $course->is_discounted,
                'has_text' => $course->has_text,
                'has_audio' => $course->has_audio,
            ],
            'gateways' => [
                'zarinpal' => config('services.zarinpal.enabled', false),
                'zibal' => true,
            ],
        ]);
    }

    public function initiatePayment(Request $request): RedirectResponse
    {
        $allowedGateways = ['zibal'];
        if (config('services.zarinpal.enabled', false)) {
            $allowedGateways[] = 'zarinpal';
        }

        $request->validate([
            'course_id'    => ['required', 'exists:courses,id'],
            'content_type' => ['required', 'in:text,audio,both'],
            'gateway'      => ['required', \Illuminate\Validation\Rule::in($allowedGateways)],
            'coupon_code'  => ['nullable', 'string'],
        ]);

        $course = Course::findOrFail($request->course_id);
        $user = Auth::user();

        if ($user->isEnrolledIn($course)) {
            return redirect()->route('courses.learn', $course->slug);
        }

        $originalAmount = $course->effective_price;
        $amount         = $originalAmount;
        $discountAmount = 0;
        $coupon         = null;

        if ($request->filled('coupon_code')) {
            $coupon = Coupon::where('code', strtoupper(trim($request->coupon_code)))->first();
            if ($coupon && $coupon->isValid() && $coupon->isValidForUser($user->id) && $coupon->isValidForAmount($originalAmount)) {
                $discountAmount = $coupon->calculateDiscount($originalAmount);
                $amount         = $originalAmount - $discountAmount;
            }
        }

        return DB::transaction(function () use ($request, $course, $user, $amount, $originalAmount, $discountAmount, $coupon) {
            $order = Order::create([
                'user_id'         => $user->id,
                'course_id'       => $course->id,
                'order_number'    => Order::generateOrderNumber(),
                'amount'          => $amount,
                'original_amount' => $originalAmount,
                'discount_amount' => $discountAmount,
                'coupon_id'       => $coupon?->id,
                'content_type'    => $request->content_type,
                'status'          => 'pending',
            ]);

            $callbackUrl = route('payment.callback', ['gateway' => $request->gateway, 'order' => $order->id]);

            if ($request->gateway === 'zarinpal') {
                $result = $this->zarinpal->request(
                    $amount,
                    "خرید دوره: {$course->title}",
                    $callbackUrl,
                    $user->mobile
                );

                if ($result['success']) {
                    Transaction::create([
                        'order_id' => $order->id,
                        'user_id' => $user->id,
                        'gateway' => 'zarinpal',
                        'authority' => $result['authority'],
                        'amount' => $amount,
                        'status' => 'initiated',
                    ]);
                    return redirect()->away($result['url']);
                }
            } else {
                $result = $this->zibal->request(
                    $amount,
                    $callbackUrl,
                    $user->mobile,
                    "خرید دوره: {$course->title}"
                );

                if ($result['success']) {
                    Transaction::create([
                        'order_id' => $order->id,
                        'user_id' => $user->id,
                        'gateway' => 'zibal',
                        'authority' => $result['track_id'],
                        'amount' => $amount,
                        'status' => 'initiated',
                    ]);
                    return redirect()->away($result['url']);
                }
            }

            $order->update(['status' => 'failed']);
            return redirect()->route('courses.show', $course->slug)
                ->with('error', 'خطا در اتصال به درگاه پرداخت.');
        });
    }

    public function callback(Request $request, string $gateway, int $order): RedirectResponse
    {
        $order = Order::with(['course', 'user'])->findOrFail($order);

        if ($order->status === 'paid') {
            return redirect()->route('courses.learn', $order->course->slug)
                ->with('success', 'پرداخت قبلاً تأیید شده است.');
        }

        $transaction = Transaction::where('order_id', $order->id)->latest()->first();

        $result = match($gateway) {
            'zarinpal' => $this->handleZarinpalCallback($request, $transaction),
            'zibal' => $this->handleZibalCallback($request, $transaction),
            default => ['success' => false, 'message' => 'درگاه پرداخت نامعتبر است.'],
        };

        if ($result['success']) {
            DB::transaction(function () use ($order, $transaction, $result) {
                $transaction->update([
                    'ref_id' => $result['ref_id'],
                    'card_number' => $result['card_number'] ?? null,
                    'status' => 'success',
                    'paid_at' => now(),
                    'gateway_response' => $result['data'] ?? null,
                ]);

                $order->update(['status' => 'paid']);

                Enrollment::create([
                    'user_id' => $order->user_id,
                    'course_id' => $order->course_id,
                    'order_id' => $order->id,
                    'content_type' => $order->content_type,
                    'enrolled_at' => now(),
                ]);

                Course::where('id', $order->course_id)->increment('students_count');

                if ($order->coupon_id && $order->discount_amount > 0) {
                    CouponUsage::create([
                        'coupon_id'       => $order->coupon_id,
                        'user_id'         => $order->user_id,
                        'order_id'        => $order->id,
                        'discount_amount' => $order->discount_amount,
                    ]);
                    Coupon::where('id', $order->coupon_id)->increment('usage_count');
                }
            });

            return redirect()->route('courses.learn', $order->course->slug)
                ->with('success', 'پرداخت با موفقیت انجام شد. دوره برای شما فعال شد.');
        }

        $transaction?->update(['status' => 'failed']);
        $order->update(['status' => 'failed']);

        return redirect()->route('courses.show', $order->course->slug)
            ->with('error', $result['message'] ?? 'پرداخت ناموفق بود.');
    }

    private function handleZarinpalCallback(Request $request, ?Transaction $transaction): array
    {
        if ($request->Status !== 'OK' || !$request->Authority) {
            return ['success' => false, 'message' => 'پرداخت لغو شد.'];
        }

        return $this->zarinpal->verify($request->Authority, $transaction->amount);
    }

    private function handleZibalCallback(Request $request, ?Transaction $transaction): array
    {
        if ($request->success != 1 || !$request->trackId) {
            return ['success' => false, 'message' => 'پرداخت لغو شد.'];
        }

        $result = $this->zibal->verify($request->trackId);
        if ($result['success']) {
            $result['ref_id'] = $result['ref_number'];
        }
        return $result;
    }
}
