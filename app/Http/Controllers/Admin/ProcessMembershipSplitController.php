<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ProcessMembershipSplitController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $data = $request->validate([
                'month' => 'nullable|string|regex:/^\d{4}-\d{2}$/',
            ]);

            $params = [];
            if (!empty($data['month'])) {
                $params['month'] = $data['month'];
            }

            // Run the Artisan command programmatically
            Artisan::call('impulse:process-membership-split', $params);
            $output = Artisan::output();

            Log::info('Admin manually processed membership split', [
                'admin_id' => auth()->id(),
                'month' => $data['month'] ?? 'default_last_month',
                'output' => $output,
            ]);

            return back()->with([
                'type' => 'success',
                'title' => __('Success / Éxito'),
                'message' => __('Revenue split calculated successfully: ') . "\n" . trim($output)
            ]);
        } catch (\Throwable $th) {
            return back()->with([
                'type' => 'error',
                'title' => __('Error / Error'),
                'message' => $th->getMessage()
            ]);
        }
    }
}
