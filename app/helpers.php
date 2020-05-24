<?php

if (!function_exists('generate_satis_homepage')) {
    /**
     * @param string $path
     * @return string
     */
    function generate_satis_homepage(string $path): string {
        return sprintf('%s/storage/%s/', rtrim(config('app.url'), '/'), trim(str_slug($path), '/'));
    }
}

if (!function_exists('create_build_folder')) {
    /**
     * Create satis repository build folder
     *
     * @param string $path Relative path to satis disk of build folder
     * @return bool
     */
    function create_build_folder(string $path): bool {
        return Storage::disk('satis_builds')->makeDirectory($path);
    }
}

if (!function_exists('link_build_folder')) {
    /**
     * Links a build folder of a satis repository to the public folder
     *
     * @param string $target Relative path to satis disk of build folder
     * @param string $link Name of the link pointing to $target
     * @return bool
     */
    function link_build_folder(string $target, string $link): bool {
        $link = storage_path(sprintf('app/public/%s', str_slug($link)));
        $target = find_build_target_to_link($target, $link);

        if (is_link($link) && readlink($link) === $target) {
            return true;
        } elseif (is_link($link)) {
            $message = [
                sprintf('The target of the link is not pointing to its build directory (%s).', $target),
                sprintf('It currently points to %s (%s).', readlink($link), basename(realpath($link))),
                'That could mean that the name of the repository is already taken.',
            ];

            throw new \RuntimeException(implode(' ', $message));
        } elseif (File::exists($link)) {
            $message = [
                sprintf('The target of the link is a regular %s.', File::isDirectory($link) ? 'directory' : 'file'),
                'That could mean that the name of the repository is already taken.',
            ];

            throw new \RuntimeException(implode(' ', $message));
        }

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return File::link($target, $link);
    }
}

if (!function_exists('unlink_build_folder')) {
    /**
     * Unlink satis repository build folder
     *
     * @param string $path Relative path to satis disk of build folder
     * @param string $link Name of the link pointing to $path
     * @return bool
     */
    function unlink_build_folder(string $path, string $link): bool {
        $link = storage_path(sprintf('app/public/%s', str_slug($link)));

        if (is_link($link) && readlink($link) === find_build_target_to_link($path, $link)) {
            unlink($link);
        }

        if (Storage::disk('satis_builds')->exists($path) && !Storage::disk('satis_builds')->allDirectories($path)) {
            return Storage::disk('satis_builds')->deleteDirectory($path);
        }

        return true;
    }
}

if (!function_exists('create_build_folder_htaccess')) {
    /**
     * Create satis repository build folder
     *
     * @param string $path Relative path to satis disk of build folder
     * @param string $reponame
     * @param string $username
     * @param string $password
     * @return bool
     */
    function create_build_folder_htaccess(string $path, string $reponame, string $username, string $password): bool {
        if (!$password) {
            return true;
        }

        $path = Storage::disk('satis_builds')->path($path);
        $crypt = Hash::make($password);
        $htaccess = implode(PHP_EOL, [
            sprintf('AuthUserFile %s', sprintf('%s/.htpasswd', $path)),
            'AuthType Basic',
            sprintf('AuthName "%s"', $reponame),
            'Require valid-user',
        ]);

        return File::put(sprintf('%s/.htaccess', $path), $htaccess . PHP_EOL)
            && File::put(sprintf('%s/.htpasswd', $path), sprintf('%s:%s', $username, $crypt) . PHP_EOL);
    }
}

if (!function_exists('satis_schema')) {
    /**
     * Reads the satis json schema from disk
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    function satis_schema(): string {
        return Storage::drive('schemas')->get('satis-schema.json');
    }
}

if (!function_exists('meta_schema')) {
    /**
     * Reads the meta json schema from disk
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    function meta_schema(): string {
        // fix missing $id attribute in schema
        $schema = json_decode(Storage::drive('schemas')->get('json-schema-draft-04.json'));
        $schema->{'$id'} = $schema->{'$schema'};

        return json_encode($schema);
    }
}

if (!function_exists('satis_templates')) {
    /**
     * Reads the satis templates from disk
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    function satis_templates(): string {
        $schema = json_decode(satis_schema(), true);
        $templates = [];

        // remove required fields from template proposals
        foreach (array_merge(['output-dir', 'twig-template'], $schema['required']) as $requiredField) {
            unset($schema['properties'][$requiredField]);
        }

        ksort($schema['properties']);

        // build templates
        foreach ($schema['properties'] as $name => $property) {
            $type = value(function () use ($property) {
                if (is_string($property['type'])) {
                    return $property['type'];
                } elseif (is_array($property['type'])) {
                    return in_array('object', $property['type'], true) ? 'object' : reset($property['type']);
                }

                return null;
            });

            $value = value(function () use ($type, $property) {
                if (array_has($property, 'properties')) {
                    $properties = [];

                    ksort($property['properties']);

                    foreach ($property['properties'] as $subName => $subProperty) {
                        // disallow setting paths
                        if ($subName === 'absolute-directory') continue;

                        $properties[$subName] = value(function () use ($subName, $subProperty) {
                            switch ($subProperty['type']) {
                                case 'array':
                                    return [$subProperty['description']];
                                case 'object':
                                    return ['field' => $subProperty['description']];
                                default:
                                    if ($subName === 'directory') return 'Setting will be automatically set and any input will be overwritten.';

                                    return $subProperty['description'];
                            }
                        });
                    }

                    return $properties;
                }

                switch ($type) {
                    case 'string':
                        return '';
                    case 'boolean':
                        return false;
                    case 'object':
                        return [];
                    default:
                        return null;
                }
            });

            $templates[] = [
                'text' => $name,
                'title' => $property['description'],
                'className' => 'jsoneditor-type-' . $type,
                'field' => $name,
                'value' => $value,
            ];
        }

        return json_encode($templates);
    }
}

if (!function_exists('find_build_target_to_link')) {
    /**
     * Tries to find a target relatively to a link
     *
     * @param string $target
     * @param string $link
     * @return string
     */
    function find_build_target_to_link(string $target, string $link) {
        $absolute = Storage::disk('satis_builds')->path($target);

        // try to find relative target
        if (starts_with($absolute, storage_path()) && starts_with($link, storage_path())) {
            $basePath = rtrim(Storage::disk('satis_builds')->path(''), '/');
            $relativePath = rtrim(str_repeat('../', count(explode('/', str_replace_first(base_path(), '', storage_path())))), '/');

            return sprintf('%s/%s',
                rtrim(str_replace_first(storage_path(), $relativePath, $basePath), '/'),
                $target
            );
        }

        // absolute target
        return $absolute;
    }
}

if (!function_exists('get_uuid_validation_rules')) {
    /**
     * @return array
     */
    function get_uuid_validation_rules(): array {
        \Illuminate\Support\Facades\Validator::extend('uuid', function ($attribute, $value, $parameters) {
            return \Ramsey\Uuid\Uuid::isValid($value);
        });

        return [
            'required',
            'uuid',
            'exists:satis_configurations,uuid',
        ];
    }
}

if (!function_exists('get_repository_validation_rules')) {
    /**
     * @param array $extra
     * @return array
     */
    function get_repository_validation_rules(array $extra = []): array {
        return array_merge(
            [
                'regex:/(?:git|ssh|https?|git@[-\w.]+):(\/\/)?(.*?)(\.git)(\/?|\#[-\d\w._]+?)$/',
                'max:255',
            ],
            $extra
        );
    }
}

if (!function_exists('get_crontab_validation_rules')) {
    /**
     * @param array $extra
     * @return array
     */
    function get_crontab_validation_rules(array $extra = []): array {
        \Illuminate\Support\Facades\Validator::extend('crontab', function ($attribute, $value, $parameters) {
            return \Cron\CronExpression::isValidExpression($value);
        });

        return array_merge(['crontab', 'nullable'], $extra);
    }
}

if (!function_exists('get_name_validation_rules')) {
    /**
     * @param string $uuid
     * @return array
     */
    function get_name_validation_rules(string $uuid): array {
        return [
            'required',
            'unique:satis_configurations' . ($uuid ? ',name,' . $uuid . ',uuid' : ''),
            'max:255',
        ];
    }
}
