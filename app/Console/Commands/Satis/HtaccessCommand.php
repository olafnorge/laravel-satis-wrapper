<?php
namespace App\Console\Commands\Satis;

use App\Models\SatisConfiguration;

class HtaccessCommand extends Command {

    /**
     * @var bool
     */
    protected $shouldLock = true;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'satis:htaccess {uuid? : UUID of the repository}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates .htaccess files to secure the repositories';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): int {
        $result = 0;

        foreach ($this->getConfigurations() as $configuration) {
            try {
                if (array_get($configuration, 'password_secured')) {
                    if (create_build_folder_htaccess(array_get($configuration, 'uuid'), array_get($configuration, 'name'), 'composer', array_get($configuration, 'password'))) {
                        $this->info(sprintf('Created .htaccess for "%s" (%s)', array_get($configuration, 'name'), array_get($configuration, 'uuid')));
                    } else {
                        $this->error(sprintf('Creating .htaccess for "%s" (%s) failed.', array_get($configuration, 'name'), array_get($configuration, 'uuid')), null, true, true);
                        $result = 1;
                    }
                }
            } catch (\Throwable $exception) {
                $this->error([
                    sprintf('Creating .htaccess for "%s" (%s) failed.', array_get($configuration, 'name'), array_get($configuration, 'uuid')),
                    $exception->getMessage(),
                ], null, true, true);
                $result = 1;
            }
        }

        return $result;
    }


    /**
     * {@inheritdoc}
     */
    protected function getValidationRules(): array {
        // nothing to validate
        return [];
    }


    /**
     * @return array
     */
    private function getConfigurations(): array {
        $uuid = $this->argument('uuid') ?: false;

        return $uuid
            ? [SatisConfiguration::where('uuid', $uuid)->first()]
            : SatisConfiguration::all()->toArray();
    }
}
