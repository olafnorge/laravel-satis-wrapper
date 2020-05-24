<?php
namespace App\Console\Commands\Satis;

use App\Models\SatisConfiguration;

class LinkCommand extends Command {

    /**
     * @var bool
     */
    protected $shouldLock = true;


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'satis:links';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Links satis build folders to public';


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int {
        $result = 0;

        foreach (SatisConfiguration::all(['uuid', 'name', 'homepage']) as $configuration) {
            try {
                if (link_build_folder($configuration->uuid, basename($configuration->homepage))) {
                    $this->info(sprintf('Created public link for "%s" (%s)', $configuration->name, $configuration->uuid));
                } else {
                    $this->error(sprintf('Creating public link for "%s" (%s) failed.', $configuration->name, $configuration->uuid), null, true, true);
                    $result = 1;
                }
            } catch (\Throwable $exception) {
                $this->error([
                    sprintf('Creating public link for "%s" (%s) failed.', $configuration->name, $configuration->uuid),
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
}
