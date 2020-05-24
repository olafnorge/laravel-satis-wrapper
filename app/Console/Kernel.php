<?php

namespace App\Console;

use App\Console\Commands\Satis\LinkCommand;
use App\Jobs\SatisBuildJob;
use App\Models\SatisConfiguration;
use DB;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Log;
use olafnorge\Console\Command\LockableTrait;

class Kernel extends ConsoleKernel {

    use LockableTrait;

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];


    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands() {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }


    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {
        // make sure to link all repositories
        // this needs to run on each instance
        $schedule
            ->command(LinkCommand::class)
            ->everyMinute();

        try {
            // only allow cron execution by one host
            if (!$this->lock('scheduler')) return;

            // make sure the database is available
            if (DB::getSchemaBuilder()->hasTable(with(new SatisConfiguration())->getTable())) {
                foreach (SatisConfiguration::where('crontab', '<>', '')->get() as $record) {
                    $schedule
                        ->job(new SatisBuildJob($record->uuid))
                        ->cron($record->crontab)
                        ->after(function () use ($record) {
                            // make sure htaccess files get created
                            // this should run only on one instance because of the shared storage
                            $success = $this->getArtisan()->call('satis:htaccess', [
                                'uuid' => $record->uuid,
                                '--wait-for-lock' => true,
                                '--no-ansi' => true,
                                '--no-interaction' => true,
                            ]) === 0;

                            if (!$success) {
                                echo $this->getArtisan()->output();
                            }
                        });
                }
            }
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage(), [
                'instance' => get_class($exception),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
        } finally {
            $this->release();
        }
    }
}
