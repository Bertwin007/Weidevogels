<?php

namespace App\Http\Controllers;

use App\Models\Observation;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaController extends Controller
{
    public function observation(Observation $observation): BinaryFileResponse
    {
        abort_unless($observation->isPublished(), 404);

        $absolute = $observation->absolutePhotoPath();

        abort_unless($absolute, 404);

        return response()->file($absolute, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
