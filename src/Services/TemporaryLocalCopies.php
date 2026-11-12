<?php

namespace Amicus\FilamentEmployeeManagement\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Local copies of default disk files for libraries that need a real path (PhpSpreadsheet Drawing).
 * The workbook reads each image only when it is written, so its owner deletes them after the write.
 */
final class TemporaryLocalCopies
{
    /** @var array<string, string|null> default disk path => local copy, null when the file is missing */
    private array $localPaths = [];

    public function of(?string $diskPath): ?string
    {
        if (! $diskPath) {
            return null;
        }

        if (! array_key_exists($diskPath, $this->localPaths)) {
            $this->localPaths[$diskPath] = Storage::exists($diskPath) ? $this->copy($diskPath) : null;
        }

        return $this->localPaths[$diskPath];
    }

    public function delete(): void
    {
        File::delete(array_filter($this->localPaths));
        $this->localPaths = [];
    }

    private function copy(string $diskPath): string
    {
        $localPath = sys_get_temp_dir() . '/' . Str::uuid() . '.' . pathinfo($diskPath, PATHINFO_EXTENSION);
        File::put($localPath, Storage::get($diskPath));

        return $localPath;
    }
}
