<?php
namespace App\Repositories;

use App\Jobs\SatisBuildJob;
use App\Models\SatisConfiguration;
use App\Models\User;
use Composer\Json\JsonValidationException;
use Seld\JsonLint\ParsingException;
use Validator;

class SatisRepositoryRepository {

    /**
     * @var SatisConfigurationRepository
     */
    private $satisConfigurationRepository;


    /**
     * SatisRepositoryRepository constructor.
     *
     * @param SatisConfigurationRepository $satisConfigurationRepository
     */
    public function __construct(SatisConfigurationRepository $satisConfigurationRepository) {
        $this->satisConfigurationRepository = $satisConfigurationRepository;
    }


    /**
     * @param SatisConfiguration $record
     * @param $url
     * @param User|null $user
     * @return bool
     * @throws JsonValidationException
     * @throws ParsingException
     * @throws \Throwable
     */
    public function create(SatisConfiguration $record, $url, User $user = null) {
        $configuration = json_decode($record->configuration, true);
        $this->validates($url, array_get($configuration, 'repositories', []));
        $configuration['repositories'][] = ['url' => $url, 'type' => 'vcs'];
        $record->configuration = json_encode($configuration);

        if ((bool) $this->satisConfigurationRepository->edit($record, $record->toArray(), $user, false)) {
            dispatch(new SatisBuildJob($record->uuid, $user->id, $url));

            return true;
        }

        return false;
    }


    /**
     * @param SatisConfiguration $record
     * @param $index
     * @param User|null $user
     * @return bool
     * @throws JsonValidationException
     * @throws ParsingException
     * @throws \Throwable
     */
    public function delete(SatisConfiguration $record, $index, User $user = null) {
        $configuration = json_decode($record->configuration, true);
        $url = array_get($configuration, sprintf('repositories.%s.url', $index));

        if (!$url) {
            throw new \RuntimeException('Repository not found');
        }

        unset($configuration['repositories'][$index]);
        $record->configuration = json_encode($configuration);

        return (bool) $this->satisConfigurationRepository->edit($record, $record->toArray(), $user);
    }


    /**
     * @param SatisConfiguration $record
     * @param $index
     * @param $newUrl
     * @param User|null $user
     * @return bool
     * @throws JsonValidationException
     * @throws ParsingException
     * @throws \Throwable
     */
    public function edit(SatisConfiguration $record, $index, $newUrl, User $user = null) {
        $configuration = json_decode($record->configuration, true);
        $url = array_get($configuration, sprintf('repositories.%s.url', $index));

        if (!$url) {
            throw new \RuntimeException('Repository not found');
        }

        if ($url === $newUrl) {
            return true;
        }

        $this->validates($newUrl, array_get($configuration, 'repositories', []));
        $configuration['repositories'][$index]['url'] = $newUrl;
        $record->configuration = json_encode($configuration);

        if ((bool) $this->satisConfigurationRepository->edit($record, $record->toArray(), $user, false)) {
            dispatch(new SatisBuildJob($record->uuid, $user->id, $newUrl));

            return true;
        }

        return false;
    }


    /**
     * @param string $url
     * @param array $repositories
     * @throws JsonValidationException
     */
    public function validates(string $url, array $repositories) {
        foreach ($repositories as $repository) {
            if (array_get($repository, 'url') === $url) {
                throw new \RuntimeException('Repository already exists');
            }
        }

        $validator = Validator::make(['url' => $url], ['url' => get_repository_validation_rules(['required'])]);

        if ($validator->fails()) {
            $errors = [];

            foreach ($validator->errors()->keys() as $key) {
                $errors[] = $key . ' : ' . $validator->errors()->first($key);
            }

            throw new JsonValidationException('The json config contains errors', $errors);
        }
    }
}
