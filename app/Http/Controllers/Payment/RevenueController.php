<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\User;
use App\Enums\Payment\PaymentStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RevenueController extends Controller
{
    public function __invoke(Request $request)
    {
        if (!$this->canAccess()) {
            throw new \Exception(__("You are not authorized to access this resource"));
        }

        $period = $request->query('period', 'monthly'); // daily, monthly, annually
        $year = (int) $request->query('year', now()->year);

        $revenueData = $this->getRevenueData($period, $year);

        return response()->json($revenueData);
    }

    private function getRevenueData(string $period, int $year): array
    {
        switch ($period) {
            case 'daily':
                return $this->getDailyRevenue($year);
            case 'monthly':
                return $this->getMonthlyRevenue($year);
            case 'annually':
                return $this->getAnnualRevenue();
            default:
                return $this->getMonthlyRevenue($year);
        }
    }

    private function getDailyRevenue(int $year): array
    {
        $month = (int) request()->query('month', now()->month);
        
        $paymentData = Payment::where('status', PaymentStatus::COMPLETED)
            ->whereYear('paid_at', $year)
            ->whereMonth('paid_at', $month)
            ->select(
                DB::raw('DAY(paid_at) as day'),
                DB::raw('SUM(amount) as revenue')
            )
            ->groupBy('day')
            ->get();

        $purchaseData = Purchase::where('status', 'completed')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->select(
                DB::raw('DAY(created_at) as day'),
                DB::raw('SUM(amount) as revenue')
            )
            ->groupBy('day')
            ->get();

        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        $result = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $pRev = (float) ($paymentData->firstWhere('day', $day)?->revenue ?? 0);
            $purRev = (float) ($purchaseData->firstWhere('day', $day)?->revenue ?? 0);
            $result[] = [
                'day' => $day,
                'revenue' => $pRev + $purRev
            ];
        }

        return $result;
    }

    private function getMonthlyRevenue(int $year): array
    {
        $paymentData = Payment::where('status', PaymentStatus::COMPLETED)
            ->whereYear('paid_at', $year)
            ->select(
                DB::raw('MONTH(paid_at) as month'),
                DB::raw('SUM(amount) as revenue')
            )
            ->groupBy('month')
            ->get();

        $purchaseData = Purchase::where('status', 'completed')
            ->whereYear('created_at', $year)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount) as revenue')
            )
            ->groupBy('month')
            ->get();

        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
        ];

        $result = [];

        foreach ($months as $monthNum => $monthName) {
            $pRev = (float) ($paymentData->firstWhere('month', $monthNum)?->revenue ?? 0);
            $purRev = (float) ($purchaseData->firstWhere('month', $monthNum)?->revenue ?? 0);
            $result[] = [
                'month' => $monthName,
                'revenue' => $pRev + $purRev
            ];
        }

        return $result;
    }

    private function getAnnualRevenue(): array
    {
        $paymentData = Payment::where('status', PaymentStatus::COMPLETED)
            ->select(
                DB::raw('YEAR(paid_at) as year'),
                DB::raw('SUM(amount) as revenue')
            )
            ->groupBy('year')
            ->get();

        $purchaseData = Purchase::where('status', 'completed')
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('SUM(amount) as revenue')
            )
            ->groupBy('year')
            ->get();

        $allYears = $paymentData->pluck('year')->merge($purchaseData->pluck('year'))->unique()->sort()->values();

        $result = [];
        foreach ($allYears as $yr) {
            $pRev = (float) ($paymentData->firstWhere('year', $yr)?->revenue ?? 0);
            $purRev = (float) ($purchaseData->firstWhere('year', $yr)?->revenue ?? 0);
            $result[] = [
                'year' => $yr,
                'revenue' => $pRev + $purRev
            ];
        }

        return $result;
    }

    public function canAccess()
    {
        $_user = User::find(Auth::user()->id);
        if ($_user && ($_user->hasRole('admin') || $_user->user_type === \App\Enums\User\UserType::ADMIN)) {
            return true;
        }
        return false;
    }
}