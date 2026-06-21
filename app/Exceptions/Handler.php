<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

use Laravel\Passport\Exceptions\OAuthServerException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //OAuthServerException::class,
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
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (OAuthServerException $e, $request) {
            return response()->json([
                'error' => 'invalid_login',
                'message' => 'Invalid login credentials'
            ], $e->statusCode());
        });
    }
}
