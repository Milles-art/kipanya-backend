<?php

namespace App\Services\Content;

use App\Models\Content\Cartoon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class CartoonMediaService
{
    private const DISK = 'public';

    public function replaceThumbnail(Cartoon $cartoon, UploadedFile $file): string
    {
        $disk = Storage::disk(self::DISK);
        $extension = strtolower($file->extension());
        $path = "cartoons/{$cartoon->id}/thumbnail/" . Str::uuid() . ".{$extension}";

        $disk->putFileAs(dirname($path), $file, basename($path));

        $oldPath = $cartoon->thumbnail_path;
        $cartoon->forceFill(['thumbnail_path' => $path])->save();

        if ($oldPath && $oldPath !== $path) {
            $disk->delete($oldPath);
        }

        return $path;
    }

    public function removeThumbnail(Cartoon $cartoon): void
    {
        if ($cartoon->thumbnail_path) {
            Storage::disk(self::DISK)->delete($cartoon->thumbnail_path);
            $cartoon->forceFill(['thumbnail_path' => null])->save();
        }
    }

    public function deleteAll(Cartoon $cartoon): void
    {
        Storage::disk(self::DISK)->deleteDirectory("cartoons/{$cartoon->id}");
    }
}
