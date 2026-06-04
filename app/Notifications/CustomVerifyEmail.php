<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmail extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
            // Получаем подписанный URL от Laravel
        $verificationUrl = $this->verificationUrl($notifiable);

        // Извлекаем путь и параметры
        $parsedUrl = parse_url($verificationUrl);
        $path = $parsedUrl['path']; 
        $query = $parsedUrl['query']; 

        // Собираем ссылку для фронтенда
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:8080');
        $url = $frontendUrl . $path . '?' . $query;

        return (new MailMessage)
            ->subject('Подтверждение email адреса')
            ->greeting('Здравствуйте!')
            ->line('Нажмите на кнопку, чтобы подтвердить email адрес.')
            ->action('Подтвердить email', $url)
            ->line('Ссылка действительна в течение 60 минут.')
            ->line('Если вы не создавали аккаунт, проигнорируйте это письмо.')
            ->salutation('С уважением, ' . config('app.name'));
    }

    protected function verificationUrl($notifiable)
    {
        // Генерируем подписанный URL как в стандартном Laravel
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
