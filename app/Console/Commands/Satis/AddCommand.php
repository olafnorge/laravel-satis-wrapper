<?php
namespace App\Console\Commands\Satis;

use App\Models\SatisConfiguration;
use App\Repositories\SatisRepositoryRepository;

class AddCommand extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'satis:add {uuid : UUID of the repository to use} {url : VCS repository URL}';

    /**
     * @var SatisRepositoryRepository
     */
    private $repository;


    /**
     * Create a new command instance.
     *
     * @throws \ReflectionException
     */
    public function __construct(SatisRepositoryRepository $repository) {
        parent::__construct();
        $this->repository = $repository;
    }


    public function handle(): int {
        $record = SatisConfiguration::where('uuid', $this->argument('uuid'))->first();

        try {
            if (!$this->repository->create($record, $this->argument('url'))) {
                $this->error(sprintf('Adding %s to %s failed.', $this->argument('url'), $record->uuid), null, true, true);

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

        $this->writeCommandStatistic($record->uuid);
        $this->info(sprintf('Added %s to %s (%s)', $this->argument('url'), $record->uuid, $record->name));

        return 0;
    }


    /**
     * {@inheritdoc}
     */
    protected function getValidationRules(): array {
        return [
            'uuid' => get_uuid_validation_rules(),
            'url' => get_repository_validation_rules(['required']),
        ];
    }
}
