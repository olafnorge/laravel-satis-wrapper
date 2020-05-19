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
     * @param  \Closure $next
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
     * @return SatisConfiguration
     */
    protected function getSatisConfiguration(Request $request) {
        return SatisConfiguration::where('uuid', $request->route('repository'))->first() ?: abort(404);
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
