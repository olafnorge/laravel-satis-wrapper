<?php

namespace App\Notifications;

use App\Models\SatisConfiguration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SatisJobSuccessNotification extends Notification implements ShouldQueue {

    use Queueable;

    /**
     * @var string
     */
    private $jobName;

    /**
     * @var string
     */
    private $repositoryUuid;


    /**
     * Create a new notification instance.
     *
     * @param string $jobName
     * @param string $repositoryUuid
     */
    public function __construct(string $jobName, string $repositoryUuid) {
        $this->jobName = $jobName;
        $this->repositoryUuid = $repositoryUuid;
        $this->queue = 'email';
    }


    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable) {
        return ['mail'];
    }


    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable) {
        $record = SatisConfiguration::where('uuid', $this->repositoryUuid)->first();

        return (new MailMessage)
            ->subject('The ' . ucfirst($this->jobName) . ' job for ' . $record->name . ' is done')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('The »' . $this->jobName . '« job you triggered has finished and the »' . $record->name . '« repository is now ready to use.')
            ->action('View Homepage', $record->homepage);
    }


    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray($notifiable) {
        return [
            //
        ];
    }
}
