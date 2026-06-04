<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Payment;
use App\Models\WatchLog;
use App\Models\Setting;
use App\Models\CreatorEarning;
use Carbon\Carbon;

class ProcessMembershipSplit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'impulse:process-membership-split {month? : The month to process (YYYY-MM), defaults to last month}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates and distributes monthly membership revenue proportionally to creators based on watch logs.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $monthInput = $this->argument('month');
        if ($monthInput) {
            $date = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        } else {
            // Default to last month
            $date = now()->subMonth()->startOfMonth();
        }

        $year = $date->year;
        $month = $date->month;
        $monthName = $date->format('F Y');

        $this->info("Processing membership split for {$monthName}...");

        // 1. Calculate total membership revenue pool
        $totalPool = (float) Payment::where('status', 'completed')
            ->whereYear('paid_at', $year)
            ->whereMonth('paid_at', $month)
            ->whereHas('plan', function($query) {
                $query->where('plan_type', 'spectator');
            })
            ->sum('amount');

        $this->info("Total completed membership revenue pool: \${$totalPool}");

        if ($totalPool <= 0) {
            $this->warn("No completed membership payments found for {$monthName}. Earning split skipped.");
            return 0;
        }

        // 2. Get platform revenue split ratio (creator percentage share)
        $creatorPoolSharePercent = (float) Setting::get('revenue_split_ratio', 50); // e.g. 60%
        $creatorPoolShare = round(($totalPool * $creatorPoolSharePercent) / 100, 2);
        $platformShare = round($totalPool - $creatorPoolShare, 2);

        $this->info("Creator revenue pool ({$creatorPoolSharePercent}%): \${$creatorPoolShare}");
        $this->info("Platform portion: \${$platformShare}");

        // 3. Count total watch seconds
        $totalWatchSeconds = (int) WatchLog::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->sum('duration_seconds');

        $this->info("Total spectator watch time: {$totalWatchSeconds} seconds");

        if ($totalWatchSeconds <= 0) {
            $this->warn("No spectator watch logs found for {$monthName}. Earning split skipped.");
            return 0;
        }

        // 4. Calculate watch seconds per creator
        $creatorWatchLogs = WatchLog::whereYear('watch_logs.created_at', $year)
            ->whereMonth('watch_logs.created_at', $month)
            ->join('contents', 'watch_logs.content_id', '=', 'contents.id')
            ->select('contents.user_id as creator_id', DB::raw('SUM(watch_logs.duration_seconds) as watch_seconds'))
            ->groupBy('contents.user_id')
            ->get();

        $this->info("Found " . $creatorWatchLogs->count() . " creators with active watch logs.");

        DB::beginTransaction();
        try {
            foreach ($creatorWatchLogs as $log) {
                $creator = User::find($log->creator_id);
                if (!$creator) continue;

                $shareFraction = $log->watch_seconds / $totalWatchSeconds;
                $earnings = round($creatorPoolShare * $shareFraction, 2);

                if ($earnings <= 0) continue;

                // Ensure we don't duplicate for this month/creator
                $description = "Membership pool split for {$date->format('Y-m')} (Proportional watch share: " . round($shareFraction * 100, 2) . "%)";
                $exists = CreatorEarning::where('creator_id', $creator->id)
                    ->where('source', 'membership_split')
                    ->where('description', $description)
                    ->exists();

                if ($exists) {
                    $this->comment("Creator ID {$creator->id} ({$creator->name}) already processed for {$monthName}. Skipping.");
                    continue;
                }

                CreatorEarning::create([
                    'creator_id' => $creator->id,
                    'amount' => $earnings,
                    'source' => 'membership_split',
                    'description' => $description,
                ]);

                $this->info("Creator ID {$creator->id} ({$creator->name}) allocated \${$earnings} ({$log->watch_seconds}s / {$totalWatchSeconds}s)");
            }

            DB::commit();
            $this->info("Membership split calculations processed successfully.");
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->error("Error processing split: " . $th->getMessage());
            return 1;
        }

        return 0;
    }
}
