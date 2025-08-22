<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Media;

class DownloadController extends Controller
{
    public function show(Request $request, Media $media): Response
    {
        /** @var Authenticatable|null $user */
        $user = Auth::user();
        abort_unless($user !== null, 403);

        return Storage::disk($media->disk)->response($media->path, $media->filename);
    }
}