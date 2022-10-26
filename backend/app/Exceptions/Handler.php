<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($this->isHttpException($exception)) {
        if (request()->expectsJson()) {
            switch ($exception->getStatusCode()) {
                case 404:
                    return response()->json(['message' => 'Invalid request or url.'], 404);
                    break;
                case '500':
                    return response()->json(['message' => 'Server error. Please contact admin.'], 500);
                    break;

                default:
                    return $this->renderHttpException($exception);
                    break;
            }
        }
        } else if ($exception instanceof ModelNotFoundException) {
            if (request()->expectsJson()) {
                return response()->json(['message' =>$exception->getMessage()], 404);
            }
        } {
            return parent::render($request, $exception);
        }
        return parent::render($request, $exception);
    }
}
