<?php

namespace App\Http\Middleware;

use App\Models\SatisConfiguration;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class AuthBasicOnceSatis {


    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     * @throws AuthenticationException
     */
    public function handle(Request $request, Closure $next) {
        if (!$this->passes($request)) {
            throw new AuthenticationException();
        }

        return $next($request);
    }


    /**
     * @param Request $request
     * @return SatisConfiguration|void
     */
    protected function getSatisConfiguration(Request $request) {
        if ($param = $request->route('repository')) {
            return SatisConfiguration::where('uuid', $param)->first() ?: abort(404);
        } elseif ($param = $request->route('any')) {
            $pathSegments = explode('/', $param, 2);
            $uuidOrHomepage = array_first($pathSegments);
            $repo = SatisConfiguration::where('uuid', $uuidOrHomepage)->first()
                ?? SatisConfiguration::where('homepage', generate_satis_homepage($uuidOrHomepage))->first();
            return $repo ?: abort(404);
        }

        return abort(404);
    }


    /**
     * @param Request $request
     * @return bool
     */
    protected function passes(Request $request) {
        $repository = $this->getSatisConfiguration($request);

        if ($repository->password_secured) {
            return $request->getPassword() === $repository->password;
        }

        return true;
    }
}
