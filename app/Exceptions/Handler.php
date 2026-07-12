<?php

namespace App\Exceptions;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use PDOException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (QueryException $e, Request $request) {
            return $this->handleDatabaseException($request);
        });

        $this->renderable(function (PDOException $e, Request $request) {
            return $this->handleDatabaseException($request);
        });
    }

    /**
     * Handle database-related exceptions with a friendly response.
     */
    protected function handleDatabaseException(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Terjadi kesalahan database. Silakan coba lagi.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan database. Silakan coba lagi.');
    }
}
