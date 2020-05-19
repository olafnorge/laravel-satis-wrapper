<?php
namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;

abstract class SatisJob implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Disable timeout of the job
     *
     * @var int
     */
    public $timeout = 0;

    /**
     * Only allow one try for a job
     *
     * @var int
     */
    public $tries = 1;

    /**
     * @var string
     */
    protected $repositoryUuid;

    /**
     * @var string
     */
    protected $userId;

    /**
     * @var bool
     */
    protected $blocking;


    /**
     * Create a new job instance.
     *
     * @param string $repositoryUuid
     * @param string $userId
     * @param bool $blocking
     */
    public function __construct(string $repositoryUuid, string $userId = '', bool $blocking = true) {
        $this->repositoryUuid = $repositoryUuid;
        $this->userId = $userId;
        $this->blocking = $blocking;
        $this->queue = 'satis';
    }


    /**
     * @param $notification
     */
    protected function notify($notification) {
        if ($user = User::where('id', $this->userId)->first()) {
            try {
                $user->notify($notification);
            } catch (\Throwable $exception) {
                Log::error($exception->getMessage(), [
                    'instance' => get_class($exception),
                    'message' => $exception->getMessage(),
                    'code' => $exception->getCode(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTraceAsString(),
                ]);
            }
        }
    }


    /**
     * @param \Throwable $exception
     * @return string
     */
    protected function getTracesAsString(\Throwable $exception) {
        $trace = $exception->getTraceAsString();

        // FIXME: find a more practical way - this is exhausting our mem
//        if ($exception->getPrevious()) {
//            $trace .= PHP_EOL . $this->getTracesAsString($exception->getPrevious());
//        }

        return $trace;
    }
}
