<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class InviteUser extends Notification
{
    use Queueable;

    protected $role;
    /**
     * Create a new notification instance.
     */
    public function __construct($role)
    {
        $this->role = $role;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {

        $url = URL::temporarySignedRoute(
            'set_password', //рут с ссылки в письме
            Carbon::now()->addHours(24),
            ['email'=>$notifiable->email] //вшили мейл пользователя в ссылку
        );

        return (new MailMessage)
            ->subject('Invite - 8Conference')
            ->view('emails.InviteUser', [
                'url' => $url,
                'notifiable'=>$notifiable,
                'role'=>$this->role
            ]);
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
