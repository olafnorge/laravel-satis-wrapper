<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;

class AuthApiToken extends AuthBasicOnceSatis {


    protected function passes(Request $request) {
        return $request->bearerToken() === config('satis.htpasswd_password');
    }
}
