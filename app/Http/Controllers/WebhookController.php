<?php

namespace App\Http\Controllers;

use App\Jobs\SatisBuildJob;
use App\Models\SatisConfiguration;
use App\Models\SatisDownloadStatistic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller {


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function satis(Request $request) :JsonResponse {
        foreach ($request->input('downloads', []) as $download) {
            /** @noinspection PhpDynamicAsStaticMethodCallInspection */
            $record = SatisDownloadStatistic::firstOrNew(
                ['package' => $download['name'], 'version' => $download['version']],
                ['package' => $download['name'], 'version' => $download['version'], 'count' => 0]
            );
            $record->count++;
            $record->save();
        }

        return response()->json([], 204);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function gitlab(Request $request) :JsonResponse {
        // get repository urls
        $urls = array_values(array_filter($request->input('repository', []), function ($key) {
            return in_array($key, ['git_http_url', 'git_ssh_url'], true);
        }, ARRAY_FILTER_USE_KEY));

        if (!$urls) {
            return response()->json(['error' => 'Bad Request'], 400);
        }

        $buildJobs = [];

        foreach (SatisConfiguration::all('uuid', 'homepage', 'configuration') as $record) {
            foreach ($record->repositories as $repository) {
                foreach ($urls as $url) {
                    if (array_get($repository, 'url') == $url) {
                        $buildJobs[] = new SatisBuildJob($record->uuid, '', $url);
                    }
                }
            }
        }

        if (!$buildJobs) {
            return response()->json(['error' => 'Not Found'], 404);
        }

        foreach ($buildJobs as $buildJob) {
            dispatch($buildJob);
        }

        return response()->json([], 204);
    }
}
