<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;

use App\Models\Movie;
use App\Models\Serie;
use App\Models\Short;
use App\Models\ChapterSerie;

class ChunkUploadService
{
    protected string $chunkPath;

    public function __construct(
        public UploadedFile $file,
        private readonly bool $isLastChunk,
        private readonly string $documentPath,
        private readonly Model $model
    ) {
        $_user = Auth::user();
        $_aux_directory = 'chunks/' . 'user_' . $_user->id;
        if (!Storage::disk(getVideoDisk())->exists($_aux_directory)) {
            Storage::disk(getVideoDisk())->makeDirectory($_aux_directory);
        }
        // Instead, you can create a unique hash or name and uuid to avoid the file to override.
        $tempFileName =  $file->getClientOriginalName();

        $this->chunkPath = Storage::disk(getVideoDisk())->path(
            $_aux_directory . "/{$tempFileName}"
        );
    }

    public function merge(): string|null
    {
        File::append($this->chunkPath, $this->file->get());

        if (!$this->isLastChunk) {
            return null;
        }

        $path = $this->documentPath . '/' . $this->file->getClientOriginalName();

        if ($this->model instanceof Movie) {
            if ($this->documentPath == 'movie_videos') {
                $this->model->updateMovieVideo($this->chunkPath);
            } else if ($this->documentPath == 'trailer_videos') {
                $this->model->updateTrailerVideo($this->chunkPath);
            }
        } else if ($this->model instanceof Short) {
            if ($this->documentPath == 'short_videos') {
                $this->model->updateVideoShort($this->chunkPath);
            }
        } else if ($this->model instanceof Serie) {
            if ($this->documentPath == 'trailer_videos') {
                $this->model->updateTrailerVideo($this->chunkPath);
            }
        } else if ($this->model instanceof ChapterSerie) {
            if ($this->documentPath == 'chapter_videos') {
                $this->model->updateChapterVideo($this->chunkPath);
            }
        }

        $this->deleteChunk();

        return $path;
    }

    public function deleteChunk(): void
    {
        Storage::disk(getVideoDisk())->delete($this->chunkPath);
    }
 }
