<?php
namespace App\Console\Commands\Satis;

use App\Repositories\SatisConfigurationRepository;

class InitCommand extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'satis:init {name : Name of the repository} {crontab? : Repository crontab (needs to be passed with quotes)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialises a composer repository';

    /**
     * @var SatisConfigurationRepository
     */
    private $repository = null;


    /**
     * Create a new command instance.
     *
     * @param SatisConfigurationRepository $repository
     */
    public function __construct(SatisConfigurationRepository $repository) {
        parent::__construct();
        $this->repository = $repository;
    }


    /**
     * @return int
     */
    public function handle() {
        try {
            $record = $this->repository->create(
                [
                    'configuration' => json_encode([ 'name' => $this->argument('name')]),
                    'crontab' => $this->argument('crontab'),
                ]
            );

            if (!$record) {
                $this->error(sprintf('Initializing repository %s failed.', $this->argument('name')), null, true, true);

                return 1;
            }
        } catch (\Throwable $exception) {
            if (is_callable([$exception, 'getErrors'])) {
                /** @noinspection PhpUndefinedMethodInspection */
                $this->error($exception->getErrors(), null, true, true);
            } elseif (is_callable([$exception, 'getDetails'])) {
                /** @noinspection PhpUndefinedMethodInspection */
                $this->error($exception->getDetails(), null, true, true);
            } else {
                $this->error($exception->getMessage(), null, true, true);
            }

            return 1;
        }

        $this->info('Repository initialized. Please proceed with configuration at: ' . route('satis.configuration.edit', ['uuid' =>  $record->uuid]));
        return 0;
    }


    /**
     * {@inheritdoc}
     */
    protected function getValidationRules(): array {
        return [
            'name' => get_name_validation_rules(''),
            'crontab' => get_crontab_validation_rules(),
        ];
    }
}
