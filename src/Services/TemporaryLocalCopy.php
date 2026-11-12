<?php

namespace Amicus\FilamentEmployeeManagement\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Local copy of a default disk file for libraries that need a real path (PhpSpreadsheet Drawing).
 */
final class TemporaryLocalCopy
{
    /**
     * The copy is deleted when the request or command terminates, because the workbook
     * reads the image only when it is written.
     */
    public static function of(?string $diskPath): ?string
    {
        if (! $diskPath || ! Storage::exists($diskPath)) {
            return null;
        }

        $localPath = sys_get_temp_dir() . '/' . Str::uuid() . '.' . pathinfo($diskPath, PATHINFO_EXTENSION);
        File::put($localPath, Storage::get($diskPath));

        app()->terminating(fn () => File::delete($localPath));

        return $localPath;
    }
}
