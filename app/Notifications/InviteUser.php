<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class InviteUser extends Notification
{
    use Queueable;

    protected $role;

    public function __construct($role)
    {
        $this->role = $role;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        $params = [
            'email' => $notifiable->email, // Декодированный email
            'expires' => $expiration = Carbon::now()->addHours(24)->timestamp,
        ];

        // Генерируем подпись
        $signature = hash_hmac('sha256', http_build_query($params, '', '&', PHP_QUERY_RFC3986), config('app.key'));

        // Формируем URL
        $url = sprintf(
            '%s/set-password?%s&signature=%s',
            rtrim($frontendUrl, '/'),
            http_build_query($params, '', '&', PHP_QUERY_RFC3986),
            $signature
        );

        Log::info('Generated invite URL: ' . $url);

        return (new MailMessage)
            ->subject('Invite - 8Conference')
            ->view('emails.invite', [
                'url' => $url,
                'notifiable' => $notifiable,
                'role' => $this->role
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
