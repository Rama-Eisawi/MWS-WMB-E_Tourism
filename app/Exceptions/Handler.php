<?php

namespace App\Exceptions;

use App\Http\Middleware\Authenticate;
use App\Traits\ApiResponses;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;

use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponses;

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];
    public function render($request, Throwable $exception)
    {
        /*if ($exception instanceof ModelNotFoundException) {
            return $this->failed('Record not found', 404);
        }*/
        if ($exception instanceof ValidationException) {
            return $this->failed($exception->errors(), 422);
        }
        if ($exception instanceof Authenticate) {
            return $this->failed('Unauthenticated, you should login', 401);
        }
        // Handling AuthenticationException for unauthenticated status
        if ($exception instanceof AuthenticationException) {
            return $this->failed('Unauthenticated, you should login', 401);
        }
        return parent::render($request, $exception);
    }
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
