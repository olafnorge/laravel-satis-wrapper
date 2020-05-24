<?php
namespace App\Jobs;

use App\Notifications\SatisJobFailedNotification;
use App\Notifications\SatisJobSuccessNotification;
use Artisan;
use RuntimeException;

class SatisPurgeJob extends SatisJob {


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        try {
            $parameters = [
                'uuid' => $this->repositoryUuid,
                '--wait-for-lock' => $this->blocking,
                '--no-ansi' => true,
                '--no-interaction' => true,
            ];

            if (Artisan::call('satis:purge', $parameters) === 0) {
                $this->notify(new SatisJobSuccessNotification('purge', $this->repositoryUuid));
                $this->delete();

                return;
            }

            $output = Artisan::output();
            $this->notify(new SatisJobFailedNotification('purge', $this->repositoryUuid, $output));
            $this->fail(new RuntimeException($output));
        } catch (\Throwable $exception) {
            $this->notify(new SatisJobFailedNotification(
                'purge',
                $this->repositoryUuid,
                $exception->getMessage() . PHP_EOL . $this->getTracesAsString($exception)
            ));
            $this->fail($exception);
        }
    }
}
