<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaController extends Controller
{
    public function show(string $path): BinaryFileResponse
    {
        abort_if(
            str_contains($path, '..')
                || str_contains($path, '\\')
                || ! preg_match('#^(branding|categories|homepage|payments|products|settings|blog/media)/[A-Za-z0-9_./-]+$#', $path),
            404
        );

        $disk = Storage::disk('media');

        abort_unless($disk->exists($path), 404);

        return response()->file($disk->path($path), [
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
