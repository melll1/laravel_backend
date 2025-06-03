<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;

class CustomVerifyEmail extends Notification
{
    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verifica tu dirección de correo')
            ->line('Haz clic en el botón para verificar tu correo electrónico.')
            ->action('Verificar Correo', $verificationUrl)
            ->line('Si no creaste esta cuenta, puedes ignorar este mensaje.');
    }

    protected function verificationUrl($notifiable)
    {
        $signedUrl = URL::temporarySignedRoute(
            'verification.verify.custom', // Asegúrate de que esta ruta exista
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        // Cambia la redirección final al frontend en Angular
        return 'http://localhost:8000' . parse_url($signedUrl, PHP_URL_PATH) . '?verified=1&email=' . urlencode($notifiable->email);
    }
}
