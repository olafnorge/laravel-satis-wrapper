<?php

namespace App\Notifications;

use App\Models\SatisConfiguration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SatisJobFailedNotification extends Notification implements ShouldQueue {

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
     * @var string
     */
    private $commandOutput;


    /**
     * Create a new notification instance.
     *
     * @param string $jobName
     * @param string $repositoryUuid
     * @param string $commandOutput
     */
    public function __construct(string $jobName, string $repositoryUuid, string $commandOutput) {
        $this->jobName = $jobName;
        $this->repositoryUuid = $repositoryUuid;
        $this->commandOutput = $commandOutput;
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
            ->subject('The ' . ucfirst($this->jobName) . ' job for ' . $record->name . ' failed')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('The »' . $this->jobName . '« job you triggered has failed. Please see attached logs for more information.')
            ->attachData($this->commandOutput, 'trace.txt');
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
