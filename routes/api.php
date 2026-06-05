<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CameraController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\RustCreateCamerasExcelFileService;
use App\Http\Controllers\RustRemakePhotosController;
use App\Http\Controllers\RustRemakeVideosController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
    ->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.reset');


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/report-events', function (Request $request) {
    return response()->stream(function () use ($request) {
        $report_id = $request->input('report_id');

        while (true) {
            $cached = Cache::get("report_status_{$report_id}", ['status' => 'pending']);

            $status = is_array($cached) ? $cached['status'] : $cached;
            $file_path = is_array($cached) ? ($cached['file_path'] ?? null) : null;

            echo "data: " . json_encode([
                'status' => $status,
                'file_path' => $file_path
            ]) . "\n\n";

            ob_flush();
            flush();

            if ($status === 'closed' || $status === 'failed') {
                break;
            }

            sleep(2);
        }
    }, 200, [
        'Content-Type' => 'text/event-stream',
        'Cache-Control' => 'no-cache',
        'X-Accel-Buffering' => 'no',
    ]);
});

    Route::post('/report-callback', function (Request $request) {
        $report_id = $request->input('report_id');
        $status = $request->input('status');
        $file_path = $request->input('file_path'); // получаем путь к файлу

        Cache::put("report_status_{$report_id}", [
            'status' => $status,
            'file_path' => $file_path
        ], 3600);

        return response()->json(["success" => $status]);
    });

    Route::get('/download-report/{report_id}', function ($report_id) {
    $reportData = Cache::get("report_status_{$report_id}");

    if (!$reportData || $reportData['status'] !== 'closed') {
        return response()->json(['error' => 'Report not ready'], 404);
    }

    $file_path = $reportData['file_path'];

    if (!file_exists($file_path)) {
        return response()->json(['error' => 'File not found'], 404);
    }

    return response()->download($file_path)->deleteFileAfterSend(true);
})->middleware('auth:sanctum');


    Route::get('/photo-events', function (Request $request) {
        return response()->stream(function () use ($request) {
            $report_id = $request->input('report_id');

           while (true) {

            $photoData = Cache::get("photo_status_{$report_id}", [
                'status' => 'pending',
                'error_files' => ""
            ]);

            $status = $photoData['status'];
            $error_files = $photoData['error_files'];

            echo "data: " . json_encode([
                'status' => $status,
                'error_files' => $error_files
            ]) . "\n\n";

            ob_flush();
            flush();

            if ($status === 'closed' || $status === 'failed') {
                break;
            }

            sleep(2);
        }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    });

    Route::post('/photo-callback', function (Request $request) {
        $report_id = $request->input('report_id');
        $status = $request->input('status');
        $error_files = $request->input('error_files');

        if ($status === 'failed') {
            Cache::put("photo_status_{$report_id}", [
                        'status' => "failed",
                        'error_files' => $error_files
             ], 3600);
        } else {
            Cache::put("photo_status_{$report_id}", [
                        'status' => "closed",
                        'error_files' => $error_files
             ], 3600);
        }

        return response()->json(["success" => $status]);
    });


    Route::get('/video-events', function (Request $request) {
        return response()->stream(function () use ($request) {
            $report_id = $request->input('report_id');

            while (true) {

            $videoData = Cache::get("video_status_{$report_id}", [
                'status' => 'pending',
                'error_files' => ""
            ]);

            $status = $videoData['status'];
            $error_files = $videoData['error_files'];

            echo "data: " . json_encode([
                'status' => $status,
                'error_files' => $error_files
            ]) . "\n\n";

            ob_flush();
            flush();

            if ($status === 'closed' || $status === 'failed') {
                break;
            }

            sleep(2);
        }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    });

    Route::post('/video-callback', function (Request $request) {
        $report_id = $request->input('report_id');
        $status = $request->input('status');
        $error_files = $request->input('error_files');

        if ($status === 'failed') {
            Cache::put("video_status_{$report_id}", [
                        'status' => "failed",
                        'error_files' => $error_files
             ], 3600);
        } else {
            Cache::put("video_status_{$report_id}", [
                        'status' => "closed",
                        'error_files' => $error_files
             ], 3600);
        }


        return response()->json(["success" => $status]);
    });


Route::middleware('auth:sanctum')->group(function (){
    Route::apiResource('cameras', CameraController::class);
    Route::delete('/cameras', [CameraController::class, 'destroyAll']);
    Route::apiResource('rust-excel', RustCreateCamerasExcelFileService::class);
    Route::apiResource('rust-photos', RustRemakePhotosController::class);
    Route::apiResource('rust-videos', RustRemakeVideosController::class);
});
