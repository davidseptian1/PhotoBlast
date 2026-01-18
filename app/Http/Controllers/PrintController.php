<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PrintController extends Controller
{
    /**
     * Serve the original collage file without any processing.
     * URL example: /print/original/davidseptian0409%40gmail.com
     */
    public function original(Request $request, $email)
    {
        $diskPath = $email . '/photo/collage/collage.jpg';

        if (! Storage::disk('public')->exists($diskPath)) {
            abort(404, 'File not found');
        }

        $fullPath = storage_path('app/public/' . $diskPath);

        return response()->file($fullPath);
    }

    /**
     * Return a simple HTML page that shows the original collage and
     * triggers the browser print dialog automatically.
     * URL example: /print/view/davidseptian0409%40gmail.com
     */
    public function view(Request $request, $email)
    {
        $imageUrl = route('print.original', ['email' => $email]);
        return view('print.original', ['imageUrl' => $imageUrl]);
    }
}
