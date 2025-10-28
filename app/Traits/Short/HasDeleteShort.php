<?php

namespace App\Traits\Short;

use App\Models\Short;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

trait HasDeleteShort
{
    public function delete(Short $short)
    {
        try {
            DB::beginTransaction();
            $short->deleteVideoShort();
            $short->content->delete();
            $short->delete();
            DB::commit();
            return $short;
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            throw $th;
        }
    }
}
