<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RustRemakePhotosController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'photos.*' => 'required|file|max:5242880',
            'title' => 'required|string',
            'callback_url' => 'required|string'
        ]);

        $validated['user_id'] = auth()->id();

        $multipartData = [];

        foreach ($validated['photos'] as $index => $file) {
            $multipartData[] = [
                'name' => "photos[$index]",  // photos[0], photos[1] etc.
                'contents' => fopen($file->getPathname(), 'r'),
                'filename' => $file->getClientOriginalName()
            ];
        }

        $multipartData[] = ['name' => 'title', 'contents' => $validated['title']];
        $multipartData[] = ['name' => 'callback_url', 'contents' => $validated['callback_url']];
        $multipartData[] = ['name' => 'user_id', 'contents' => $validated['user_id']];

    try {
        $response = Http::timeout(1800)->asMultipart()->post('http://localhost:3001/images', $multipartData);

        if ($response->successful()) {
            return response()->json([
                'success' => true,
                'data' => $response->json()
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Ошибка в Rust сервисе',
                'details' => $response->json() ?? $response->body(),
                'rust_status' => $response->status()
            ], 422);
        }

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'error' => 'Rust сервис недоступен',
            'message' => $e->getMessage()
        ], 503);
    }
}
}
