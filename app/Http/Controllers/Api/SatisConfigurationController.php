<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SatisConfiguration;
use App\Repositories\SatisConfigurationRepository;
use Composer\Json\JsonValidationException;
use Illuminate\Http\Request;
use Seld\JsonLint\ParsingException;
use Throwable;

class SatisConfigurationController extends Controller {

    /**
     * @var SatisConfigurationRepository
     */
    private $satisConfigurationRepository;


    /**
     * SatisConfigurationController constructor.
     *
     * @param SatisConfigurationRepository $satisConfigurationRepository
     */
    public function __construct(SatisConfigurationRepository $satisConfigurationRepository) {
        $this->satisConfigurationRepository = $satisConfigurationRepository;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index() {
        return response()->json(SatisConfiguration::paginate(50)->toArray());
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request) {
        $name = $request->json('name', '');
        $secured = $request->json('secured', 1);
        $crontab = $request->json('crontab', '');
        $repositories = $request->json('repositories', []);
        $require = $request->json('require', []);

        try {
            $result = $this->satisConfigurationRepository->create([
                'configuration' => json_encode([
                    'name' => $name,
                    'repositories' => $repositories,
                    'require' => $require,
                ]),
                'password_secured' => $secured,
                'crontab' => $crontab,
            ]);

            return response()->json($result->toArray());
        } catch (JsonValidationException $exception) {
            return response()->json(['code' => 422, 'errors' => $exception->getErrors()], 422);
        } catch (ParsingException $exception) {
            return response()->json(['code' => 400, 'errors' => ['json' => 'Could not parse JSON']], 400);
        } catch (Throwable $exception) {
            return response()->json(['code' => 500, 'errors' => ['generic' => 'Internal Server Error']], 500);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id) {
        $record = SatisConfiguration::where('uuid', $id)->first();
        abort_unless($record, 404);

        return response()->json($record->toArray());
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id) {
        $record = SatisConfiguration::where('uuid', $id)->first();
        abort_unless($record, 404);

        $configuration = json_decode($record->configuration, true);
        $name = $request->json('name', $record->name);
        $secured = $request->json('secured', $record->password_secured);
        $crontab = $request->json('crontab', $record->crontab);
        $repositories = value(function () use ($request, $configuration): array {
            $repositories = array_get($configuration, 'repositories', []);

            foreach ($request->json('repositories', $repositories) as $repository) {
                $url = strtolower(array_get($repository, 'url'));
                $type = strtolower(array_get($repository, 'type'));

                // find already existing record
                $existing = array_first($repositories, function ($item) use ($url) {
                    return strtolower(array_get($item, 'url')) === $url;
                });

                // replace existing record if type does not match
                if ($existing && strtolower(array_get($existing, 'type')) !== $type) {
                    $repositories = array_map(function ($item) use ($url, $repository) {
                        if (strtolower(array_get($item, 'url')) === $url) {
                            $item = $repository;
                        }

                        return $item;
                    }, $repositories);
                } elseif (!$existing) {
                    $repositories[] = $repository;
                }
            }

            return $repositories;
        });
        $require = array_merge(
            array_get($configuration, 'require', []),
            $request->json('require', array_get($configuration, 'require', []))
        );

        try {
            $result = $this->satisConfigurationRepository->edit($record, [
                'configuration' => json_encode(array_merge(
                    $configuration,
                    [
                        'name' => $name,
                        'repositories' => $repositories,
                        'require' => $require,
                    ]
                )),
                'password_secured' => $secured,
                'crontab' => $crontab,
            ], null, false);

            return response()->json($result->toArray(), 200);
        } catch (JsonValidationException $exception) {
            return response()->json(['code' => 422, 'errors' => $exception->getErrors()], 422);
        } catch (ParsingException $exception) {
            return response()->json(['code' => 400, 'errors' => ['json' => 'Could not parse JSON']], 400);
        } catch (Throwable $exception) {
            return response()->json(['code' => 500, 'errors' => ['generic' => 'Internal Server Error']], 500);
        }
    }
}
