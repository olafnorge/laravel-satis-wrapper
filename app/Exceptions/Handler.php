<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Handler extends ExceptionHandler {


    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
    ];


    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $exception
     * @return void
     * @throws Exception
     */
    public function report(Exception $exception) {
        if ($this->shouldReport($exception)) {
            Log::error($exception);
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $exception) {
        switch (true) {
            case $exception instanceof TokenMismatchException:
                return redirect()->back()->withInput()->with('error', 'Your session expired. Please try again.');
            case $exception instanceof NotFoundHttpException:
                return $request->wantsJson()
                    ? response()->json(['code' => 404], 404)
                    : $this->renderHttpException($exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception) {
        $unauthorizedHttpException = new UnauthorizedHttpException(
            sprintf('Basic realm="%s"', config('app.name')),
            'Unauthenticated.'
        );

        if ($request->expectsJson()) {
            return response()->json(
                ['error' => 'Unauthenticated.'],
                $unauthorizedHttpException->getStatusCode(),
                $unauthorizedHttpException->getHeaders()
            );
        } elseif ($request->route()->getName() === 'satis.repository.show') {
            return $this->renderHttpException($unauthorizedHttpException);
        }

        return redirect()->guest(route('index'));
    }
}
