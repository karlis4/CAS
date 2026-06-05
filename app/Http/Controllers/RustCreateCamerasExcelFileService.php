<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RustCreateCamerasExcelFileService extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fileName' => 'required',
            'callback_url' => 'required|string',
            'cameras' => 'required|array',
            'cameras.*.real_camera_id' => 'required|string',
            'cameras.*.name' => 'required|string',
            'cameras.*.adress' => 'required|string',
            'cameras.*.latitude' => 'required|string',
            'cameras.*.longitude' => 'required|string',
            'cameras.*.status' => 'required|string',
            'cameras.*.current_corp' => 'required|string',
            'cameras.*.current_person' => 'required|string',
            'cameras.*.date_expluatation' => 'required|string',
            'cameras.*.date_guarantee' => 'required|string',
            'cameras.*.invent_number' => 'required|string',
        ], [
            'fileName.required' => 'имя не задано',
            'fullFilePath.required' => 'путь не задан'
        ]);

    try {
        $response = Http::timeout(50)->post('http://localhost:3000/', [
        'cameras' => $validated['cameras'],
        'file_info' => [
            'file_name' => $validated['fileName'],
            'full_file_path' => '/var/www/reports/'
        ],
        'callback_url' => $validated['callback_url']
    ]);

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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
