<?php

namespace App\Notifications;

use App\Models\Holiday;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HolidayAddedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Holiday $holiday) {}

    /** @return string[] */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('🎉 Hari Libur: '.$this->holiday->name)
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Terdapat hari libur yang akan datang.')
            ->line('**'.$this->holiday->name.'**')
            ->line('📅 Tanggal: '.$this->holiday->date->translatedFormat('l, d F Y'));

        if ($this->holiday->description) {
            $message->line($this->holiday->description);
        }

        return $message
            ->line('Terima kasih telah menggunakan '.config('app.name').'.');
    }
}
