<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    /**
     * Store an image for the text cards and return the address to put in the editor.
     * SVG is not accepted: it can carry scripts.
     */
    public function image(Request $request): JsonResponse
    {
        abort_unless($request->user()->canAny(['cards.create', 'cards.update']), 403);

        $request->validate([
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp', 'max:4096'],
        ]);

        $path = $request->file('image')->store('card-images', 'public');

        return response()->json(['url' => '/storage/'.$path]);
    }
}
