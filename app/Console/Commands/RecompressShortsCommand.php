<?php

namespace App\Console\Commands;

use App\Models\Short;
use App\Services\VideoCompressorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;

class RecompressShortsCommand extends Command
{
    protected $signature = 'shorts:recompress
                            {--quality=720p : Quality preset (480p, 720p, 1080p)}
                            {--max-size=5 : Only recompress files larger than this size in MB}
                            {--dry-run : Show what would be recompressed without doing it}
                            {--id= : Recompress a specific short by ID}';

    protected $description = 'Download, recompress, and re-upload oversized short videos to improve streaming performance';

    public function handle()
    {
        $quality = $this->option('quality');
        $maxSizeMB = (float) $this->option('max-size');
        $dryRun = $this->option('dry-run');
        $specificId = $this->option('id');

        $compressor = new VideoCompressorService();
        $disk = Storage::disk(getDisk());

        // Get shorts to process
        $query = Short::whereNotNull('short_video')->where('short_video', '!=', '');
        if ($specificId) {
            $query->where('id', $specificId);
        }
        $shorts = $query->get();

        $this->info("Found {$shorts->count()} shorts with videos.");
        $this->info("Recompressing files > {$maxSizeMB} MB at {$quality} quality.");
        if ($dryRun) {
            $this->warn("DRY RUN — no files will be modified.");
        }
        $this->newLine();

        $processed = 0;
        $skipped = 0;
        $errors = 0;
        $totalSaved = 0;

        foreach ($shorts as $short) {
            $path = $short->short_video;
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            try {
                $size = $disk->size($path);
            } catch (\Exception $e) {
                $this->error("  ID {$short->id}: Cannot read file — {$e->getMessage()}");
                $errors++;
                continue;
            }

            $sizeMB = round($size / 1024 / 1024, 2);

            // Skip small files (already compressed)
            if ($sizeMB <= $maxSizeMB) {
                $this->line("  <fg=gray>ID {$short->id}: {$sizeMB} MB ({$ext}) — OK, skipping</>");
                $skipped++;
                continue;
            }

            $this->info("  ID {$short->id}: {$sizeMB} MB ({$ext}) — NEEDS COMPRESSION");

            if ($dryRun) {
                continue;
            }

            // Download from S3 to temp
            $tempInput = tempnam(sys_get_temp_dir(), 'short_in_') . '.' . $ext;
            $tempOutput = tempnam(sys_get_temp_dir(), 'short_out_') . '.mp4';

            try {
                // 1. Download
                $this->line("    ↓ Downloading from S3...");
                $stream = $disk->readStream($path);
                file_put_contents($tempInput, $stream);
                fclose($stream);

                $inputSize = filesize($tempInput);
                $this->line("    ↓ Downloaded: " . round($inputSize / 1024 / 1024, 2) . " MB");

                // 2. Compress with FFmpeg
                $this->line("    ⚙ Compressing at {$quality}...");
                $compressor->compressVideo($tempInput, $tempOutput, $quality);

                $outputSize = filesize($tempOutput);
                $outputSizeMB = round($outputSize / 1024 / 1024, 2);
                $savings = round(($inputSize - $outputSize) / 1024 / 1024, 2);
                $pctSaved = $inputSize > 0 ? round((1 - $outputSize / $inputSize) * 100, 1) : 0;

                $this->line("    ✓ Compressed: {$outputSizeMB} MB (saved {$savings} MB / {$pctSaved}%)");

                // 3. Upload compressed version to S3 (new .mp4 path)
                $directory = dirname($path);
                $newFilename = uniqid('short_') . '.mp4';
                $newPath = $directory . '/' . $newFilename;

                $this->line("    ↑ Uploading to S3...");
                $handle = fopen($tempOutput, 'rb');
                $disk->put($newPath, $handle);
                fclose($handle);

                // 4. Delete old file from S3
                $disk->delete($path);

                // 5. Update DB
                $short->forceFill(['short_video' => $newPath])->save();

                $totalSaved += $savings;
                $processed++;
                $this->info("    ✅ Done! New path: {$newPath}");

            } catch (\Exception $e) {
                $this->error("    ✗ Error: {$e->getMessage()}");
                $errors++;
            } finally {
                // Cleanup temp files
                if (file_exists($tempInput)) unlink($tempInput);
                if (file_exists($tempOutput)) unlink($tempOutput);
            }

            $this->newLine();
        }

        $this->newLine();
        $this->info("═══════════════════════════════════════");
        $this->info("  Processed: {$processed}");
        $this->info("  Skipped:   {$skipped}");
        $this->info("  Errors:    {$errors}");
        $this->info("  Saved:     " . round($totalSaved, 2) . " MB total");
        $this->info("═══════════════════════════════════════");
    }
}
