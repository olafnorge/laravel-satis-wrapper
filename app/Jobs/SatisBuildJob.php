<?php
namespace App\Jobs;

use App\Notifications\SatisJobFailedNotification;
use App\Notifications\SatisJobSuccessNotification;
use Artisan;
use RuntimeException;

class SatisBuildJob extends SatisJob {

    /**
     * @var string
     */
    private $repositoryUrl;


    /**
     * Create a new job instance.
     *
     * @param string $repositoryUuid
     * @param string $userId
     * @param string $repositoryUrl
     * @param bool $blocking
     */
    public function __construct(string $repositoryUuid, string $userId = '', string $repositoryUrl = '', bool $blocking = true) {
        parent::__construct($repositoryUuid, $userId, $blocking);
        $this->repositoryUrl = $repositoryUrl;
    }


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

            if ($this->repositoryUrl) {
                $parameters['--repository-url'] = $this->repositoryUrl;
            }

            if (Artisan::call('satis:build', $parameters) === 0) {
                $this->notify(new SatisJobSuccessNotification('build', $this->repositoryUuid));
                $this->delete();

                return;
            }

            $output = Artisan::output();
            $this->notify(new SatisJobFailedNotification('build', $this->repositoryUuid, $output));
            $this->fail(new RuntimeException($output));
        } catch (\Throwable $exception) {
            $this->notify(new SatisJobFailedNotification(
                'build',
                $this->repositoryUuid,
                $exception->getMessage() . PHP_EOL . $this->getTracesAsString($exception)
            ));
            $this->fail($exception);
        }
    }
}
