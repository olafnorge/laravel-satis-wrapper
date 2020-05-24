<?php
namespace App\Console\Commands\Satis;

use App\Console\Command as BaseCommand;

abstract class Command extends BaseCommand {

    use InteractsWithSatisCommand;

    /**
     * @var bool
     */
    protected $shouldLock = false;


    public function __construct() {
        parent::__construct();

        if ($this->shouldLock()) {
            $this->addOption(
                'wait-for-lock',
                null,
                null,
                'Wait until a lock was acquired and then then run the command'
            );
        }
    }
}
