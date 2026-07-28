<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Morilog\Jalali\Jalalian;

class ExpenseController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $period = $request->string('period', 'month')->toString();
        if (! in_array($period, ['week', 'month', 'custom'], true)) {
            $period = 'month';
        }

        [$from, $to] = $this->resolveDateRange($period, $request);

        $expenses = Expense::query()
            ->where('user_id', $user->id)
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->get();

        $weekStart = now()->subDays(6)->startOfDay();
        $weekEnd = now()->endOfDay();
        $jalaliNow = Jalalian::fromCarbon(now());
        $monthStart = Jalalian::fromFormat('Y/m/d', "{$jalaliNow->getYear()}/{$jalaliNow->getMonth()}/1")->toCarbon()->startOfDay();
        $monthEnd = Jalalian::fromFormat('Y/m/d', "{$jalaliNow->getYear()}/{$jalaliNow->getMonth()}/{$jalaliNow->getMonthDays()}")->toCarbon()->endOfDay();

        $weekTotal = (int) Expense::query()
            ->where('user_id', $user->id)
            ->whereBetween('expense_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->sum('amount');

        $monthTotal = (int) Expense::query()
            ->where('user_id', $user->id)
            ->whereBetween('expense_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->sum('amount');

        $periodTotal = (int) $expenses->sum('amount');

        $chart = $this->buildChartData($expenses, $from, $to);

        return Inertia::render('Dashboard/Expenses', [
            'filters' => [
                'period' => $period,
                'from' => toJalali($from),
                'to' => toJalali($to),
            ],
            'summary' => [
                'week_total' => $weekTotal,
                'month_total' => $monthTotal,
                'period_total' => $periodTotal,
                'expense_count' => $expenses->count(),
            ],
            'chart' => $chart,
            'expenses' => $expenses->map(fn (Expense $e) => [
                'id' => $e->id,
                'title' => $e->title,
                'amount' => $e->amount,
                'expense_date' => toJalali($e->expense_date),
            ])->values(),
            'today_jalali' => toJalali(now()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'amount' => ['required', 'integer', 'min:1'],
            'expense_date' => ['required', 'string', 'max:20'],
        ], [
            'title.required' => 'عنوان هزینه الزامی است.',
            'amount.required' => 'مبلغ الزامی است.',
            'amount.min' => 'مبلغ باید بیشتر از صفر باشد.',
            'expense_date.required' => 'تاریخ الزامی است.',
        ]);

        $gregorian = jalaliToGregorian($data['expense_date']);
        if (! $gregorian) {
            return back()->withErrors(['expense_date' => 'تاریخ شمسی معتبر نیست. فرمت: ۱۴۰۴/۰۵/۰۱'])->withInput();
        }

        Expense::create([
            'user_id' => Auth::id(),
            'title' => $data['title'],
            'amount' => (int) persianToEnglishNumber((string) $data['amount']),
            'expense_date' => $gregorian->toDateString(),
        ]);

        return back()->with('success', 'هزینه با موفقیت ثبت شد.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        abort_unless($expense->user_id === Auth::id(), 403);

        $expense->delete();

        return back()->with('success', 'هزینه حذف شد.');
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveDateRange(string $period, Request $request): array
    {
        if ($period === 'week') {
            return [now()->subDays(6)->startOfDay(), now()->endOfDay()];
        }

        if ($period === 'custom') {
            $from = jalaliToGregorian((string) $request->input('from', '')) ?? now()->subDays(29)->startOfDay();
            $to = jalaliToGregorian((string) $request->input('to', '')) ?? now()->endOfDay();

            if ($from->gt($to)) {
                [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }

            return [$from->startOfDay(), $to->endOfDay()];
        }

        $jalaliNow = Jalalian::fromCarbon(now());
        $from = Jalalian::fromFormat('Y/m/d', "{$jalaliNow->getYear()}/{$jalaliNow->getMonth()}/1")->toCarbon()->startOfDay();
        $to = Jalalian::fromFormat('Y/m/d', "{$jalaliNow->getYear()}/{$jalaliNow->getMonth()}/{$jalaliNow->getMonthDays()}")->toCarbon()->endOfDay();

        return [$from, $to];
    }

    private function buildChartData($expenses, Carbon $from, Carbon $to): array
    {
        $grouped = $expenses->groupBy(fn (Expense $e) => $e->expense_date->toDateString());
        $points = [];
        $cursor = $from->copy()->startOfDay();

        while ($cursor->lte($to)) {
            $key = $cursor->toDateString();
            $dayExpenses = $grouped->get($key, collect());

            $points[] = [
                'date' => $key,
                'jalali' => toJalali($cursor),
                'total' => (int) $dayExpenses->sum('amount'),
                'items' => $dayExpenses->map(fn (Expense $e) => [
                    'title' => $e->title,
                    'amount' => $e->amount,
                ])->values()->all(),
            ];

            $cursor->addDay();
        }

        return $points;
    }
}
