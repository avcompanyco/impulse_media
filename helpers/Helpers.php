<?php

use Illuminate\Support\Facades\Storage;


function getLocale()
{
    return config('app.locale');
}

function slugify($text)
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    if (empty($text)) {
        return 'n-a';
    }
    return $text;
}

/**
 * Funcion para actualizar muchos registros con diferentes datos
 * 
 * @param mixed $table
 * @param mixed $values
 * @param mixed $key
 * @return string
 */
function batchUpdate($table, $values, $key)
{
    $query = "UPDATE {$table} SET ";
    $columns = array_keys(reset($values));

    foreach ($columns as $column) {
        $query .= "{$column} = CASE {$key} ";
        foreach ($values as $data) {
            $query .= "WHEN {$data[$key]} THEN '{$data[$column]}' ";
        }
        $query .= "END, ";
    }

    $query = rtrim($query, ', ') . " WHERE {$key} IN (" . implode(',', array_column($values, $key)) . ")";
    return $query;
}

/**
 * Inertia succes handler
 * @param mixed $title
 * @param mixed $message
 * @return Illuminate\Http\RedirectResponse
 */
function inertiaSuccessHandler($title = "", $message = "")
{
    return redirect()
        ->back()
        ->with([
            "type" => "success",
            "title" => $title,
            "message" => $message
        ]);
}

/**
 * Inertia Error Handler
 * @param mixed $title
 * @param mixed $message
 * @return Illuminate\Http\RedirectResponse
 */
function inertiaErrorHandler($title = "", $message = "")
{
    return redirect()
        ->back()
        ->withErrors([
            "type" => "error",
            "title" => $title,
            "message" => $message
        ]);
}

function googleMapEmbed($q, $center = "", $zoom = 15, $language = "", $region = "")
{
    $url_gmap = 'https://www.google.com/maps/embed/v1/search?key=' . env('GOOGLE_MAP_API');
    $url_gmap .= "&q={$q}";
    if ($center != "") {
        $url_gmap .= "&center={$center}";
    }
    if ($zoom > 0 && $zoom < 22) {
        $url_gmap .= "&zoom={$zoom}";
    }
    if ($language != "") {
        $url_gmap .= "&language={$language}";
    }
    if ($region != "") {
        $url_gmap .= "&region={$region}";
    }

    return $url_gmap;
}


/**
 * Get Disk
 */
function getDisk()
{
    return config('filesystems.default', 'local');
}

/**
 * Get Video Disk
 */
function getVideoDisk()
{
    return 'local';
}

/**
 * Safely ensure a directory exists on the storage disk.
 * S3 doesn't have real directories, so this silently skips errors.
 */
function ensureStorageDirectory(string $path, ?string $disk = null): void
{
    $disk = $disk ?? getDisk();
    
    // S3 doesn't need explicit directory creation
    if ($disk === 's3') {
        return;
    }

    try {
        if (!Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->makeDirectory($path);
        }
    } catch (\Throwable $e) {
        // Silently handle - putFile will create the path on most drivers
    }
}