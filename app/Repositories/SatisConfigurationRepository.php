<?php
namespace App\Repositories;

use App\Jobs\SatisBuildJob;
use App\Jobs\SatisPurgeJob;
use App\Models\SatisConfiguration;
use App\Models\User;
use Composer\Json\JsonValidationException;
use Cron\CronExpression;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use JsonSchema\Validator as JsonValidator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;

class SatisConfigurationRepository {


    /**
     * @param array $parameters
     * @param User|null $user
     * @return SatisConfiguration
     * @throws JsonValidationException
     * @throws ParsingException
     * @throws \Throwable
     */
    public function create(array $parameters, User $user = null) :SatisConfiguration {
        return DB::transaction(function () use($parameters, $user) {
            $configuration = value(function () use ($parameters) {
                $configuration = array_get($parameters, 'configuration', []);

                return is_array($configuration)
                    ? $configuration
                    : json_decode($configuration, true);
            });

            $parameters['uuid'] = $this->generateUuid()->toString();
            $parameters['name'] = array_get($configuration, 'name');
            $parameters['homepage'] = generate_satis_homepage(array_get($configuration, 'name'));

            $this->validate($parameters);
            $record = SatisConfiguration::create($parameters);

            try {
                if (!create_build_folder($record->uuid)) {
                    throw new \RuntimeException('Could not create build directory');
                }

                if (!link_build_folder($record->uuid, basename($record->homepage))) {
                    throw new \RuntimeException('Could not link build directory');
                }

                if ($record->password_secured && !create_build_folder_htaccess($record->uuid, $record->name, 'composer', $record->password)) {
                    throw new \RuntimeException('Could not create .htaccess');
                }
            } catch (\RuntimeException $exception) {
                unlink_build_folder($record->uuid, basename($record->homepage));
                throw $exception;
            }

            if ($record->repositories) {
                dispatch(new SatisBuildJob($record->uuid, $user->id));
            }

            return $record;
        }, 1);
    }


    /**
     * @param SatisConfiguration $record
     * @param array $parameters
     * @param User|null $user
     * @param bool $dispatchJob
     * @return SatisConfiguration
     * @throws JsonValidationException
     * @throws ParsingException
     * @throws \Throwable
     */
    public function edit(SatisConfiguration $record, array $parameters, User $user = null, $dispatchJob = true) :SatisConfiguration {
        return DB::transaction(function () use($record, $parameters, $user, $dispatchJob) {
            $configuration = value(function () use ($parameters) {
                $configuration = array_get($parameters, 'configuration', []);

                return is_array($configuration)
                    ? $configuration
                    : json_decode($configuration, true);
            });

            $parameters['uuid'] = $record->uuid;
            $parameters['name'] = array_get($configuration, 'name');
            $parameters['homepage'] = $record->homepage;

            $this->validate($parameters);
            $record->name = array_get($parameters, 'name');
            $record->password_secured = array_get($parameters, 'password_secured');
            $record->configuration = array_get($parameters, 'configuration');
            $record->crontab = array_get($parameters, 'crontab');

            if (!$record->save()) {
                throw new \RuntimeException('Saving record failed.');
            }

            try {
                if (!create_build_folder($record->uuid)) {
                    throw new \RuntimeException('Could not create build directory');
                }

                if (!link_build_folder($record->uuid, basename($record->homepage))) {
                    throw new \RuntimeException('Could not link build directory');
                }

                if ($record->password_secured && !create_build_folder_htaccess($record->uuid, $record->name, 'composer', $record->password)) {
                    throw new \RuntimeException('Could not create .htaccess');
                }
            } catch (\RuntimeException $exception) {
                unlink_build_folder($record->uuid, basename($record->homepage));
                throw $exception;
            }

            if ($record->repositories && $dispatchJob) {
                dispatch(new SatisBuildJob($record->uuid, $user->id));
            }

            return $record;
        }, 1);
    }


    /**
     * @param SatisConfiguration $record
     * @param User|null $user
     * @return bool
     * @throws JsonValidationException
     * @throws ParsingException
     */
    public function build(SatisConfiguration $record, User $user = null) :bool {
        $this->validate($record->toArray());

        if ($record->repositories) {
            dispatch(new SatisBuildJob($record->uuid, $user->id));
        }

        return true;
    }


    /**
     * @param SatisConfiguration $record
     * @param User|null $user
     * @return bool
     * @throws JsonValidationException
     * @throws ParsingException
     */
    public function purge(SatisConfiguration $record, User $user = null) {
        $this->validate($record->toArray());
        dispatch(new SatisPurgeJob($record->uuid, $user->id));

        return true;
    }


    /**
     * @return \Ramsey\Uuid\UuidInterface
     */
    private function generateUuid() :UuidInterface {
        return Uuid::uuid5(Uuid::NAMESPACE_DNS, uniqid());
    }


    /**
     * @param array $configuration
     * @return bool
     * @throws JsonValidationException
     */
    public function validate(array $configuration) :bool {
        ini_set('memory_limit', -1);

        Validator::extend('uuid', function($attribute, $value, $parameters) {
            return Uuid::isValid($value);
        });
        Validator::extend('crontab', function($attribute, $value, $parameters) {
            return CronExpression::isValidExpression($value);
        });
        Validator::extend('json', function($attribute, $value, $parameters) {
            return (new static())->validateJSON($value);
        });

        $uuid = array_get($configuration, 'uuid', '');
        $validator = Validator::make($configuration, [
            'name' => get_name_validation_rules($uuid),
            'homepage' => 'required|url|unique:satis_configurations' . ($uuid ? ',homepage,' . $uuid . ',uuid' : ''),
            'uuid' => 'required|uuid|unique:satis_configurations' . ($uuid ? ',uuid,' . $uuid . ',uuid' : ''),
            'configuration' => 'required|json',
            'crontab' => get_crontab_validation_rules(),
        ]);

        if ($validator->fails()) {
            $errors = [];

            foreach ($validator->errors()->keys() as $key) {
                $errors[] = $key . ' : ' . $validator->errors()->first($key);
            }

            throw new JsonValidationException('The json config contains errors', $errors);
        }

        return true;
    }


    /**
     * @param $setting
     * @return bool
     * @throws JsonValidationException
     * @throws ParsingException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function validateJSON(string $setting) :bool {
        $parser = new JsonParser();
        $result = $parser->lint($setting);

        // give up early
        if ($result instanceof ParsingException) {
            throw $result;
        }

        // validate against schema
        $validator = new JsonValidator();
        $validator->check(json_decode($setting), json_decode(satis_schema()));

        if (!$validator->isValid()) {
            $errors = [];

            foreach ((array) $validator->getErrors() as $error) {
                $property = array_get($error, 'property');
                $errors[] = ($property ? $property . ' : ' : '') . $error['message'];
            }

            throw new JsonValidationException('The json config does not match the expected JSON schema', $errors);
        }

        return true;
    }
}
