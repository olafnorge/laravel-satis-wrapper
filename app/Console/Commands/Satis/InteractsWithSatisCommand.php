<?php
namespace App\Console\Commands\Satis;

use App\Models\SatisConfiguration;
use App\Models\SatisJobStatistic;
use Carbon\Carbon;
use Composer\Command\BaseCommand;
use File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use olafnorge\Console\Command\LockableTrait;
use RuntimeException;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

trait InteractsWithSatisCommand {

    use LockableTrait;

    /**
     * @var BaseCommand
     */
    protected $command;

    /**
     * @var Carbon
     */
    protected $startedAt;

    /**
     * @var array
     */
    protected $outBuffer = [];


    /**
     * Removes a configuration from disk
     *
     * @param string $uuid
     * @return bool
     */
    protected function removeConfigurationFromDisk(string $uuid): bool {
        if (File::exists($this->getLocationOfBuildFile($uuid))) {
            return File::delete($this->getLocationOfBuildFile($uuid));
        }

        return true;
    }


    /**
     * Write configuration to disk
     *
     * @param SatisConfiguration $record
     * @return bool
     */
    protected function writeConfigurationToDisk(SatisConfiguration $record): bool {
        $data = json_decode($record->configuration, true);

        // fix the deprecation warning for naming conventions
        $data['name'] = value(function () use ($data) {
            if (Str::contains($data['name'], '/')) {
                $parts = explode('/', $data['name'], 2);
                $vendor = Str::lower($parts[0]);
                $project = Str::slug($parts[1], '_');
            } elseif (Str::contains(basename($data['homepage']), '-')) {
                $parts = explode('-', basename($data['homepage']), 2);
                $vendor = Str::lower($parts[0]);
                $project = Str::slug($parts[1], '_');
            } else {
                $vendor = 'satis';
                $project = Str::slug($data['name'], '_');
            }

            return sprintf('%s/%s', $vendor, $project);
        });

        // settings that tell satis how to act while downloading and dumping packages
        // refer to: https://getcomposer.org/doc/06-config.md
        $data['config']['discard-changes'] = true; // always use remote as source of truth
        $data['config']['github-expose-hostname'] = false; // ensure OAuth tokens to access the github API will have a date instead of the machine hostname
        $data['config']['github-protocols'] = ['https', 'ssh', 'git']; // only use secure git protocols
        $data['config']['htaccess-protect'] = false; // no need to protect composer's home, cache, and data directories
        $data['config']['notify-on-install'] = false; // do not notify package maintainer about download
        $data['config']['store-auths'] = false; // do not try to store auths because app runs in non-interactive mode
        $data['config']['use-include-path'] = false; // do not use php's include path

        // add a notification url to collect statistics
        $data['notify-batch'] = route('webhook.satis', ['repository' => $record->uuid]);

        // merge in authentication tokens
        $data = value(function () use ($data): array {
            if (config('satis.github_oauth')) {
                $data['config']['github-oauth'] = config('satis.github_oauth');
            }

            if (config('satis.gitlab_oauth')) {
                $data['config']['gitlab-oauth'] = config('satis.gitlab_oauth');
            }

            if (config('satis.gitlab_token')) {
                $data['config']['gitlab-token'] = config('satis.gitlab_token');
            }

            if (config('satis.http_basic')) {
                $data['config']['http-basic'] = config('satis.http_basic');
            }

            if (config('satis.bitbucket_oauth')) {
                $data['config']['bitbucket-oauth'] = config('satis.bitbucket_oauth');
            }

            return $data;
        });

        // merge in auth domains
        $data = value(function () use ($data): array {
            if (config('satis.github_oauth')) {
                $data['config']['github-domains'][] = 'github.com';

                foreach (array_keys(config('satis.github_oauth')) as $githubDomain) {
                    if ($githubDomain === 'github.com') continue;
                    $data['config']['github-domains'][] = $githubDomain;
                }

                $data['config']['github-domains'] = array_unique($data['config']['github-domains']);
            }

            if (config('satis.github_domains')) {
                $data['config']['github-domains'][] = 'github.com';

                foreach (config('satis.github_domains') as $githubDomain) {
                    if ($githubDomain === 'github.com') continue;
                    $data['config']['github-domains'][] = $githubDomain;
                }

                $data['config']['github-domains'] = array_unique($data['config']['github-domains']);
            }

            if (config('satis.gitlab_oauth')) {
                $data['config']['gitlab-domains'][] = 'gitlab.com';

                foreach (array_keys(config('satis.gitlab_oauth')) as $githubDomain) {
                    if ($githubDomain === 'gitlab.com') continue;
                    $data['config']['gitlab-domains'][] = $githubDomain;
                }

                $data['config']['gitlab-domains'] = array_unique($data['config']['gitlab-domains']);
            }

            if (config('satis.gitlab_token')) {
                $data['config']['gitlab-domains'][] = 'gitlab.com';

                foreach (array_keys(config('satis.gitlab_token')) as $githubDomain) {
                    if ($githubDomain === 'gitlab.com') continue;
                    $data['config']['gitlab-domains'][] = $githubDomain;
                }

                $data['config']['gitlab-domains'] = array_unique($data['config']['gitlab-domains']);
            }

            if (config('satis.gitlab_domains')) {
                $data['config']['gitlab-domains'][] = 'gitlab.com';

                foreach (config('satis.gitlab_domains') as $githubDomain) {
                    if ($githubDomain === 'gitlab.com') continue;
                    $data['config']['gitlab-domains'][] = $githubDomain;
                }

                $data['config']['gitlab-domains'] = array_unique($data['config']['gitlab-domains']);
            }

            return $data;
        });

        // provide custom twig template
        $data['twig-template'] = resource_path('assets/satis/index.html.twig');

        return (bool)File::put($this->getLocationOfBuildFile($record->uuid), json_encode($data), true);
    }


    /**
     * @param string $uuid
     */
    protected function writeCommandStatistic(string $uuid) {
        $runtime = (float)now()->diff($this->startedAt)->format('%s.%f');

        // prevent from updating always all rows with the same uuid
        if ($record = SatisJobStatistic::where('uuid', $uuid)->where('job', $this->getName())->first()) {
            $avgRuntime = ($record->avg_runtime + $runtime) / 2.0;
            $count = $record->count + 1;
            SatisJobStatistic::where('uuid', $uuid)
                ->where('job', $this->getName())
                ->update([
                    'avg_runtime' => $avgRuntime,
                    'count' => $count,
                ]);
        } else {
            SatisJobStatistic::create([
                'uuid' => $uuid,
                'job' => $this->getName(),
                'avg_runtime' => $runtime,
                'count' => 1,
            ]);
        }
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->startedAt = now();
        $argumentValidator = Validator::make(
        // merge arguments over options to give arguments precedence
            array_merge($this->options(), $this->arguments()),
            $this->getValidationRules()
        );
        $optionValidator = Validator::make(
        // merge options over arguments to give options precedence
            array_merge($this->arguments(), $this->options()),
            $this->getValidationRules()
        );

        if ($argumentValidator->fails() || $optionValidator->fails()) {
            $messages = array_unique(array_merge(
                $argumentValidator->messages()->all(),
                $optionValidator->messages()->all()
            ));

            throw new InvalidArgumentException(implode(PHP_EOL, $messages));
        }

        if ($this->shouldLock()) {
            if ($this->option('wait-for-lock')) {
                $this->getOutput()->write(sprintf('<info>%s </info>', 'Waiting for a lock'));

                while (!$this->lock($this->getLockName())) {
                    $this->getOutput()->write(sprintf('<info>%s</info>', '.'));
                    sleep(1);
                }

                $this->getOutput()->newLine();
                $this->info('Lock acquired.');
            } elseif (!$this->lock($this->getLockName())) {
                $this->error('Can not acquire a lock.');

                return 255;
            }
        }

        return value(function () use ($input, $output) {
            $result = parent::execute($input, $output);

            if ($this->shouldLock()) {
                $this->release();
            };

            return $result;
        });
    }


    /**
     * @return string
     */
    protected function getLockName(): string {
        return $this->getName();
    }


    /**
     * @param string $uuid
     * @return string
     */
    protected function getLocationOfBuildFile(string $uuid): string {
        return storage_path(sprintf('satis/build-%s.json', $uuid));
    }


    /**
     *
     * @return array
     */
    protected function getCommandArguments(): array {
        $arguments = [];

        foreach ($this->arguments() as $argument => $value) {
            if ($argument === 'command' || $value === $this->getName()) {
                continue;
            } elseif ($argument === 'uuid' && $value) {
                $arguments[] = $this->getLocationOfBuildFile($value);
            } elseif ($value) {
                $arguments[] = $value;
            }
        }

        return $arguments;
    }


    /**
     *
     * @return array
     */
    protected function getCommandOptions(): array {
        $options = [];

        foreach ($this->options() as $option => $value) {
            if ($option === 'wait-for-lock') {
                continue;
            } elseif ($value === true) {
                $options[] = starts_with($option, '--') ? $option : sprintf('--%s', $option);
            } elseif ($value !== null && $value !== false) {
                $options[] = sprintf(
                    '%s=%s',
                    starts_with($option, '--') ? $option : sprintf('--%s', $option),
                    $value
                );
            }
        }

        return $options;
    }


    /**
     * Gets validation rules required for the command
     *
     * @return array
     */
    protected function getValidationRules(): array {
        throw new RuntimeException('Rules need to be defined in command.');
    }


    /**
     * Run the actual satis composer process
     *
     * @return bool
     */
    protected function process() {
        $process = new Process(array_merge(
            [sprintf('%s/vendor/bin/satis', base_path()), $this->command->getName()],
            $this->getCommandOptions(),
            $this->getCommandArguments()
        ));
        $process->setTimeout(null);
        $process->setIdleTimeout(null);
        $process->disableOutput();
        $this->info(sprintf('Running command: %s', $process->getCommandLine()));
        $process->run(function ($type, $buffer) {
            if ($buffer === PHP_EOL) {
                $this->flushBuffer($type);
            } else {
                $this->addToBuffer($type, $buffer);
            }
        });

        return $process->isSuccessful();
    }


    /**
     * @param string $type
     * @param string $string
     */
    protected function addToBuffer(string $type, string $string) {
        $this->outBuffer[$type][] = $string;
    }


    /**
     * @param string $type
     */
    protected function flushBuffer(string $type) {
        if (empty($this->outBuffer[$type])) return;

        if (Process::ERR === $type) {
            $this->error(array_filter($this->outBuffer[$type]));
        } else {
            $this->info(array_filter($this->outBuffer[$type]));
        }

        $this->outBuffer[$type] = [];
    }


    /**
     * Clone arguments and options from base command
     *
     * @param BaseCommand $command
     * @throws \ReflectionException
     */
    protected function setup(BaseCommand $command) {
        $this->command = $command;

        // read arguments and exchange 'file' argument with uuid
        foreach ($command->getDefinition()->getArguments() as $inputArgument) {
            $class = get_class($inputArgument);
            $reflection = new \ReflectionClass($class);
            $modeProperty = $reflection->getProperty('mode');
            $modeProperty->setAccessible(true);
            $mode = $modeProperty->getValue($inputArgument);

            if ($inputArgument->getName() === 'file') {
                $this->addArgument('uuid', InputArgument::REQUIRED, 'UUID of the repository to use', null);
            } else {
                $this->addArgument($inputArgument->getName(), $mode, $inputArgument->getDescription(), $inputArgument->getDefault());
            }
        }

        // clone available options
        foreach ($command->getDefinition()->getOptions() as $inputOption) {
            $class = get_class($inputOption);
            $reflection = new \ReflectionClass($class);
            $modeProperty = $reflection->getProperty('mode');
            $modeProperty->setAccessible(true);
            $mode = $modeProperty->getValue($inputOption);

            if ($mode === InputOption::VALUE_NONE) {
                $this->addOption($inputOption->getName(), $inputOption->getShortcut(), $mode, $inputOption->getDescription());
            } else {
                $this->addOption($inputOption->getName(), $inputOption->getShortcut(), $mode, $inputOption->getDescription(), $inputOption->getDefault());
            }
        }

        $this->setDescription($command->getDescription());
        $this->setHelp($command->getHelp());
    }


    /**
     * @return bool
     */
    protected function shouldLock(): bool {
        return $this->shouldLock ?: false;
    }
}
