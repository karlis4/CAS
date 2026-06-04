<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Routing\Controller;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, $id, $hash)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        }

        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Неверная ссылка подтверждения'], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email уже подтвержден']);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Редирект на страницу успеха во Vue приложении
        return redirect(env('FRONTEND_URL') . '/email-verified?success=true');
    }

    public function send(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email уже подтвержден']);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Ссылка для подтверждения отправлена на ваш email']);
    }
}
