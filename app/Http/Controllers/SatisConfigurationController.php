<?php

namespace App\Http\Controllers;

use App\Models\SatisConfiguration;
use App\Models\SatisJobStatistic;
use App\Repositories\SatisConfigurationRepository;
use Carbon\Carbon;
use Composer\Json\JsonValidationException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Seld\JsonLint\ParsingException;

class SatisConfigurationController extends Controller {

    /**
     * @var SatisConfigurationRepository
     */
    private $satisConfigurationRepository;


    /**
     * SatisConfigurationController constructor.
     * @param SatisConfigurationRepository $satisConfigurationRepository
     */
    public function __construct(SatisConfigurationRepository $satisConfigurationRepository) {
        $this->satisConfigurationRepository = $satisConfigurationRepository;
    }


    /**
     * @return \Illuminate\View\View
     */
    public function index() {
        return view('satis_configuration.index', [
            'configurations' => SatisConfiguration::orderBy('name', 'asc')->paginate(15),
        ]);
    }


    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function create() {
        $name = sprintf('Repository of %s', Auth::getUser()->name);
        $homepage = generate_satis_homepage($name);
        $record = new SatisConfiguration([
            'name' => $name,
            'homepage' => $homepage,
            'configuration' => json_encode(['name' => $name, 'homepage' => $homepage]),
            'crontab' => '',
        ]);

        return view('satis_configuration.create', [
            'record' => $record,
            'templates' => satis_templates(),
            'metaSchema' => meta_schema(),
            'schema' => satis_schema(),
        ]);
    }


    /**
     * @param $uuid
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function edit($uuid) {
        $record = SatisConfiguration::where('uuid', $uuid)->first();

        if (!$record) {
            return redirect()->route('satis.configuration.index')->with('error', 'Configuration not found.');
        }

        return view('satis_configuration.create', [
            'record' => $record,
            'templates' => satis_templates(),
            'metaSchema' => meta_schema(),
            'schema' => satis_schema(),
        ]);
    }


    /**
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function store(Request $request) {
        $parameters = $request->all();
        $parameters['password_secured'] = array_get($parameters, 'password_secured', 0);

        // create a record stub for the exception handling
        $record = new SatisConfiguration();
        $record->configuration = array_get($parameters, 'configuration');
        $record->crontab = array_get($parameters, 'crontab', '');
        $record->password_secured = array_get($parameters, 'password_secured');

        try {
            if ($this->satisConfigurationRepository->create($parameters, Auth::user())) {
                return redirect()->route('satis.configuration.index')->with('success', 'Configuration created.');
            }
        } catch (JsonValidationException $exception) {
            $request->session()->now('error', $exception->getMessage());

            return view('satis_configuration.create', [
                'record' => $record,
                'templates' => satis_templates(),
                'metaSchema' => meta_schema(),
                'schema' => satis_schema(),
            ])->withErrors(['configuration' => $exception->getErrors()]);
        } catch (ParsingException $exception) {
            $request->session()->now('error', $exception->getMessage());
            $record->configuration = '{}';

            return view('satis_configuration.create', [
                'record' => $record,
                'templates' => satis_templates(),
                'metaSchema' => meta_schema(),
                'schema' => satis_schema(),
            ]);
        } catch (\Throwable $exception) {
            $request->session()->now('error', $exception->getMessage());

            return view('satis_configuration.create', [
                'record' => $record,
                'templates' => satis_templates(),
                'metaSchema' => meta_schema(),
                'schema' => satis_schema(),
            ]);
        }

        $request->session()->now('error', 'Unknown error.');

        return view('satis_configuration.create', [
            'record' => $record,
            'templates' => satis_templates(),
            'metaSchema' => meta_schema(),
            'schema' => satis_schema(),
        ]);
    }


    /**
     * @param Request $request
     * @param $uuid
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function update(Request $request, $uuid) {
        $record = SatisConfiguration::where('uuid', $uuid)->first();

        if (!$record) {
            return redirect()->route('satis.configuration.index')->with('error', 'Configuration not found.');
        }

        try {
            if ($this->satisConfigurationRepository->edit($record, $request->all(), Auth::user())) {
                return redirect()->route('satis.configuration.index')->with('success', 'Configuration edited.');
            }
        } catch (JsonValidationException $exception) {
            $request->session()->now('error', $exception->getMessage());

            return view('satis_configuration.create', [
                'record' => $record,
                'templates' => satis_templates(),
                'metaSchema' => meta_schema(),
                'schema' => satis_schema(),
            ])->withErrors(['configuration' => $exception->getErrors()]);
        } catch (ParsingException $exception) {
            $request->session()->now('error', $exception->getMessage());
            $record->configuration = '{}';

            return view('satis_configuration.create', [
                'record' => $record,
                'templates' => satis_templates(),
                'metaSchema' => meta_schema(),
                'schema' => satis_schema(),
            ]);
        } catch (\Throwable $exception) {
            $request->session()->now('error', $exception->getMessage());

            return view('satis_configuration.create', [
                'record' => $record,
                'templates' => satis_templates(),
                'metaSchema' => meta_schema(),
                'schema' => satis_schema(),
            ]);
        }

        $request->session()->now('error', 'Unknown error.');

        return view('satis_configuration.create', [
            'record' => $record,
            'templates' => satis_templates(),
            'metaSchema' => meta_schema(),
            'schema' => satis_schema(),
        ]);
    }


    /**
     * @param Request $request
     * @param $uuid
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function details(Request $request, $uuid) {
        $configuration = SatisConfiguration::where('uuid', $uuid)->first();

        if (!$configuration) {
            return redirect()->route('satis.configuration.index')->with('error', 'Configuration not found.');
        }

        $repositories = $configuration->repositories;
        $page = $request->get('page', 1);
        $perPage = 15;
        $offSet = ($page * $perPage) - $perPage;
        $repositoriesForCurrentPage = array_slice($repositories, $offSet, $perPage, true);
        $avgBuildTime = SatisJobStatistic::where('uuid', $configuration->uuid)
            ->where('job', 'satis:build')
            ->value('avg_runtime');

        $paginate = new LengthAwarePaginator(
            $repositoriesForCurrentPage,
            count($repositories),
            $perPage,
            $page,
            ['path' => route('satis.configuration.details', ['uuid' => $uuid])]
        );

        return view('satis_configuration.details', [
            'configuration' => $configuration,
            'avgBuildTime' => $avgBuildTime
                ? Carbon::createFromTimestamp($avgBuildTime)->diffForHumans(Carbon::createFromTimestamp(0.0), true)
                : null,
            'repositories' => $paginate,
            'metaSchema' => meta_schema(),
            'schema' => satis_schema(),
        ]);
    }


    /**
     * @param $uuid
     * @return \Illuminate\Http\RedirectResponse
     */
    public function build($uuid) {
        $configuration = SatisConfiguration::where('uuid', $uuid)->first();

        if (!$configuration) {
            return redirect()->route('satis.configuration.index')->with('error', 'Configuration not found.');
        }

        try {
            if ($this->satisConfigurationRepository->build($configuration, Auth::user())) {
                return redirect()->route('satis.configuration.index')->with('success', 'Build scheduled.');
            }
        } catch (\Throwable $exception) {
            return redirect()->route('satis.configuration.index')->with('error', $exception->getMessage());
        }

        return redirect()->route('satis.configuration.index')->with('error', 'Unknown error.');
    }


    /**
     * @param $uuid
     * @return \Illuminate\Http\RedirectResponse
     */
    public function purge($uuid) {
        $configuration = SatisConfiguration::where('uuid', $uuid)->first();

        if (!$configuration) {
            return redirect()->route('satis.configuration.index')->with('error', 'Configuration not found.');
        }

        try {
            if ($this->satisConfigurationRepository->purge($configuration, Auth::user())) {
                return redirect()->route('satis.configuration.index')->with('success', 'Purge scheduled.');
            }
        } catch (\Throwable $exception) {
            return redirect()->route('satis.configuration.index')->with('error', $exception->getMessage());
        }

        return redirect()->route('satis.configuration.index')->with('error', 'Unknown error.');
    }
}
