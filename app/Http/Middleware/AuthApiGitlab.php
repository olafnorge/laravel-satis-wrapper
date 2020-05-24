<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;

class AuthApiGitlab extends AuthBasicOnceSatis {


    /**
     * {@inheritdoc}
     */
    protected function passes(Request $request) {
        $repository = $this->getSatisConfiguration($request);

        if ($repository->password_secured) {
            return $request->header('X-Gitlab-Token') === $repository->password;
        }

        return true;
    }
}
