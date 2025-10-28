<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
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
        $year = $request->query('year', now()->year);

        $revenueData = $this->getRevenueData($period, $year);

        return response()->json($revenueData);
    }

    private function getRevenueData(string $period, int $year): array
    {
        $baseQuery = Payment::where('status', PaymentStatus::COMPLETED)
            ->whereYear('paid_at', $year);

        switch ($period) {
            case 'daily':
                return $this->getDailyRevenue($baseQuery, $year);
            case 'monthly':
                return $this->getMonthlyRevenue($baseQuery, $year);
            case 'annually':
                return $this->getAnnualRevenue($baseQuery);
            default:
                return $this->getMonthlyRevenue($baseQuery, $year);
        }
    }

    private function getDailyRevenue($query, int $year): array
    {
        $month = request()->query('month', now()->month);
        
        $data = $query->whereMonth('paid_at', $month)
            ->select(
                DB::raw('DAY(paid_at) as day'),
                DB::raw('SUM(amount) as revenue')
            )
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        $result = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dayData = $data->firstWhere('day', $day);
            $result[] = [
                'day' => $day,
                'revenue' => $dayData ? (float) $dayData->revenue : 0
            ];
        }

        return $result;
    }

    private function getMonthlyRevenue($query, int $year): array
    {
        $data = $query->select(
                DB::raw('MONTH(paid_at) as month'),
                DB::raw('SUM(amount) as revenue')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
        ];

        $result = [];

        foreach ($months as $monthNum => $monthName) {
            $monthData = $data->firstWhere('month', $monthNum);
            $result[] = [
                'month' => $monthName,
                'revenue' => $monthData ? (float) $monthData->revenue : 0
            ];
        }

        return $result;
    }

    private function getAnnualRevenue($query): array
    {
        $data = $query->select(
                DB::raw('YEAR(paid_at) as year'),
                DB::raw('SUM(amount) as revenue')
            )
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        return $data->map(function ($item) {
            return [
                'year' => $item->year,
                'revenue' => (float) $item->revenue
            ];
        })->toArray();
    }

    public function canAccess()
    {
        $_user = User::find(Auth::user()->id);
        if ($_user && $_user->hasRole('admin')) {
            return true;
        }
        return false;
    }
}