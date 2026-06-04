<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CameraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cameras = auth()->user()->cameras()->with('exploitationInfo')->get();

        if ($cameras->isEmpty()) {
            return response()->json(['error' => 'Камеры не найдены'], 404);
        }

        return response()->json($cameras);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'real_camera_id' => 'required|unique:cameras',
            'name' => 'required|string|max:255',
            'adress' => 'required|string',
            'latitude' => 'required|string',
            'longitude' => 'required|string',
            'status' => 'sometimes|in:online,offline,recording',

            // Поля для зависимой таблицы
            'currentCorp' => 'nullable|string',
            'currentPerson' => 'nullable|string',
            'dateExpluatation' => 'nullable|string',
            'dateGuarantee' => 'nullable|string',
            'inventNumber' => 'nullable|string',
        ]);

         // Создаем в транзакции
        $camera = DB::transaction(function () use ($validated) {
            // 1. Создаем камеру для текущего пользователя
            $camera = auth()->user()->cameras()->create([
                'real_camera_id' => $validated['real_camera_id'],
                'name' => $validated['name'],
                'adress' => $validated['adress'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'status' => $validated['status']
            ]);

            // 2. Создаем эксплуатационную информацию с camera_id из созданной камеры
            $camera->exploitationInfo()->create([
                'currentCorp' => $validated['currentCorp'] ?? null,
                'currentPerson' => $validated['currentPerson'] ?? null,
                'dateExpluatation' => $validated['dateExpluatation'] ?? null,
                'dateGuarantee' => $validated['dateGuarantee'] ?? null,
                'inventNumber' => $validated['inventNumber'] ?? null,
            ]);

            return $camera;
        });

        return response()->json($camera->load('exploitationInfo'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Находим камеру только если она принадлежит текущему пользователю
        $camera = auth()->user()->cameras()->with('exploitationInfo')->findOrFail($id);
        return response()->json($camera);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Находим камеру пользователя
        $camera = auth()->user()->cameras()->findOrFail($id);

        $validated = $request->validate([
            'real_camera_id' => 'nullable|unique:cameras,real_camera_id,' . $camera->id,
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'status' => 'sometimes|in:online,offline,recording',

            'currentCorp' => 'nullable|string',
            'currentPerson' => 'nullable|string',
            'dateExpluatation' => 'nullable|string',
            'dateGuarantee' => 'nullable|string',
            'inventNumber' => 'nullable|string',
        ]);

        DB::transaction(function () use ($camera, $validated) {
            // Обновляем камеру
            $camera->update([
                'real_camera_id' => $validated['real_camera_id'] ?? $camera->real_camera_id,
                'name' => $validated['name'] ?? $camera->name,
                'address' => $validated['address'] ?? $camera->address,
                'latitude' => $validated['latitude'] ?? $camera->latitude,
                'longitude' => $validated['longitude'] ?? $camera->longitude,
                'status' => $validated['status'] ?? $camera->status
            ]);

            // Обновляем или создаем эксплуатационную информацию
            if ($camera->exploitationInfo) {
                $camera->exploitationInfo->update([
                    'currentCorp' => $validated['currentCorp'] ?? $camera->exploitationInfo->currentCorp,
                    'currentPerson' => $validated['currentPerson'] ?? $camera->exploitationInfo->currentPerson,
                    'dateExpluatation' => $validated['dateExpluatation'] ?? $camera->exploitationInfo->dateExpluatation,
                    'dateGuarantee' => $validated['dateGuarantee'] ?? $camera->exploitationInfo->dateGuarantee,
                    'inventNumber' => $validated['inventNumber'] ?? $camera->exploitationInfo->inventNumber,
                ]);
            } else {
                $camera->exploitationInfo()->create([
                    'currentCorp' => $validated['currentCorp'] ?? null,
                    'currentPerson' => $validated['currentPerson'] ?? null,
                    'dateExpluatation' => $validated['dateExpluatation'] ?? null,
                    'dateGuarantee' => $validated['dateGuarantee'] ?? null,
                    'inventNumber' => $validated['inventNumber'] ?? null,
                ]);
            }
        });

        return response()->json($camera->load('exploitationInfo'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $camera = auth()->user()->cameras()->findOrFail($id);
        $camera->delete();

        return response()->json(['message' => 'Камера удалена']);
    }

    public function destroyAll()
    {
        auth()->user()->cameras()->delete();

        return response()->json(['message' => 'Все камеры удалены']);
    }
}
