<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AnnouncementPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Announcement $announcement) {}

    /** @return string[] */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $excerpt = strip_tags($this->announcement->content);
        $excerpt = mb_strlen($excerpt) > 200 ? mb_substr($excerpt, 0, 200).'…' : $excerpt;

        return (new MailMessage)
            ->subject('📢 Pengumuman: '.$this->announcement->title)
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Ada pengumuman baru dari manajemen.')
            ->line('**'.$this->announcement->title.'**')
            ->line($excerpt)
            ->action('Buka Aplikasi HRIS', route('mobile.home'))
            ->line('Terima kasih telah menggunakan '.config('app.name').'.');
    }
}
