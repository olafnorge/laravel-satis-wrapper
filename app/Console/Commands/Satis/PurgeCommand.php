<?php
namespace App\Console\Commands\Satis;

use App\Models\SatisConfiguration;
use Composer\Satis\Console\Command\PurgeCommand as BasePurgeCommand;

class PurgeCommand extends Command {

    /**
     * @var bool
     */
    protected $shouldLock = true;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'satis:purge';


    /**
     * Create a new command instance.
     *
     * @throws \ReflectionException
     * @return void
     */
    public function __construct() {
        parent::__construct();

        $this->setup(new BasePurgeCommand());
    }


    /**
     * @return int
     */
    public function handle(): int {
        $record = SatisConfiguration::where('uuid', $this->argument('uuid'))->first();

        if (!$this->writeConfigurationToDisk($record)) {
            $this->error('Could not write configuration to disk.', null, true, true);
            return 1;
        }

        if (!$this->argument('output-dir')) {
            $this->input->setArgument('output-dir', $record->output_dir);
        }

        $result = $this->process();
        $this->removeConfigurationFromDisk($record->uuid);

        if (!$result) {
            $this->error(sprintf('Running %s failed.', $this->getName()), null, true, true);

            return 1;
        }

        $this->writeCommandStatistic($record->uuid);

        return 0;
    }


    /**
     * {@inheritdoc}
     */
    protected function getLockName(): string {
        return $this->argument('uuid');
    }


    /**
     * {@inheritdoc}
     */
    protected function getValidationRules(): array {
        return [
            'uuid' => get_uuid_validation_rules(),
            'output-dir' => 'sometimes|nullable',
        ];
    }
}
