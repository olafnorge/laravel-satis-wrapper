<?php
namespace App\Http\Controllers;

use App\Models\SatisConfiguration;
use App\Repositories\SatisRepositoryRepository;
use Composer\Json\JsonValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SatisRepositoryController extends Controller {

    /**
     * @var SatisRepositoryRepository
     */
    private $satisRepositoryRepository;


    /**
     * SatisConfigurationController constructor.
     *
     * @param SatisRepositoryRepository $satisRepositoryRepository
     */
    public function __construct(SatisRepositoryRepository $satisRepositoryRepository) {
        $this->satisRepositoryRepository = $satisRepositoryRepository;
    }


    /**
     * @param $uuid
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function create($uuid) {
        if (!SatisConfiguration::where('uuid', $uuid)->exists()) {
            return redirect()->route('satis.configuration.index')->with('error', 'Configuration not found.');
        }

        return view('satis_repository.create');
    }


    /**
     * @param $uuid
     * @param $index
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit($uuid, $index) {
        $record = SatisConfiguration::where('uuid', $uuid)->first();

        if (!$record) {
            return redirect()->route('satis.configuration.index')->with('error', 'Configuration not found.');
        }

        $configuration = json_decode($record->configuration, true);
        $url = array_get($configuration, sprintf('repositories.%s.url', $index - 1));

        if (!$url) {
            return redirect()->route('satis.configuration.details', ['uuid' => $uuid])->with('error', 'Repository not found.');
        }

        return view('satis_repository.create', [
            'url' => $url,
        ]);
    }


    /**
     * @param Request $request
     * @param $uuid
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function store(Request $request, $uuid) {
        $record = SatisConfiguration::where('uuid', $uuid)->first();

        if (!$record) {
            return redirect()->route('satis.configuration.index')->with('error', 'Configuration not found.');
        }

        try {
            if ($this->satisRepositoryRepository->create($record, $request->input('url'), Auth::user())) {
                return redirect()->route('satis.configuration.details', ['uuid' => $uuid])->with('success', 'Configuration edited.');
            }
        } catch (JsonValidationException $exception) {
            $request->session()->now('error', $exception->getMessage());

            return view('satis_repository.create', [
                'url' => $request->input('url'),
            ])->withErrors(['url' => $exception->getErrors()]);
        } catch (\Throwable $exception) {
            $request->session()->now('error', $exception->getMessage());

            return view('satis_repository.create', [
                'url' => $request->input('url'),
            ]);
        }

        $request->session()->now('error', 'Unknown error.');

        return view('satis_repository.create', [
            'url' => $request->input('url'),
        ]);
    }


    /**
     * @param Request $request
     * @param $uuid
     * @param $index
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function update(Request $request, $uuid, $index) {
        $record = SatisConfiguration::where('uuid', $uuid)->first();

        if (!$record) {
            return redirect()->route('satis.configuration.index')->with('error', 'Configuration not found.');
        }

        try {
            if ($this->satisRepositoryRepository->edit($record, $index - 1, $request->input('url'), Auth::user())) {
                return redirect()->route('satis.configuration.details', ['uuid' => $uuid])->with('success', 'Configuration edited.');
            }
        } catch (JsonValidationException $exception) {
            $request->session()->now('error', $exception->getMessage());

            return view('satis_repository.create', [
                'url' => $request->input('url'),
            ])->withErrors(['url' => $exception->getErrors()]);
        } catch (\Throwable $exception) {
            $request->session()->now('error', $exception->getMessage());

            return view('satis_repository.create', [
                'url' => $request->input('url'),
            ]);
        }

        $request->session()->now('error', 'Unknown error.');

        return view('satis_repository.create', [
            'url' => $request->input('url'),
        ]);
    }


    /**
     * @param $uuid
     * @param $index
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($uuid, $index) {
        $record = SatisConfiguration::where('uuid', $uuid)->first();

        if (!$record) {
            return redirect()->route('satis.configuration.index')->with('error', 'Configuration not found.');
        }

        try {
            if ($this->satisRepositoryRepository->delete($record, $index - 1, Auth::user())) {
                return redirect()->route('satis.configuration.details', ['uuid' => $uuid])->with('success', 'Configuration edited.');
            }
        } catch (\Throwable $exception) {
            return redirect()->route('satis.configuration.details', ['uuid' => $uuid])->with('error', $exception->getMessage());
        }

        return redirect()->route('satis.configuration.details', ['uuid' => $uuid])->with('error', 'Unknown error.');
    }
}
