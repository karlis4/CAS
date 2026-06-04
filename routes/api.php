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
                $status = Cache::get("report_status_{$report_id}", "pending");

                echo "data: " . json_encode([
                    'status' => $status
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

        if ($status === 'failed') {
            Cache::put("report_status_{$report_id}", "failed", 3600);
        } else {
            Cache::put("report_status_{$report_id}", "closed", 3600);
        }


        return response()->json(["success" => $status]);
    });


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
