<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The path the user should be redirected to when they are not authenticated.
     *
     * @var string|null
     */
    protected $redirectTo = '/login';

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @param  Throwable  $exception
     * @return Response
     */
    public function render($request, Throwable $exception)
    {
        // Handle 405 Method Not Allowed specifically for admin routes
        if ($exception instanceof MethodNotAllowedHttpException) {
            // Check if this is related to admin routes
            if (str_starts_with($request->getPathInfo(), '/admin')) {
                // Log the specific issue for debugging
                \Log::warning('405 Method Not Allowed for admin route', [
                    'path' => $request->getPathInfo(),
                    'method' => $request->getMethod(),
                    'user' => auth()->id() ? auth()->user()->email : 'not authenticated',
                    'session' => session()->all()
                ]);
                
                // If user is authenticated and trying to access admin, redirect to admin-bridge
                if (auth()->check() && auth()->user()->canAccessAdmin()) {
                    return redirect('/admin-bridge')->with('error', 'Ocorreu um erro ao acessar o painel administrativo. Redirecionando...');
                }
                
                // Otherwise, redirect to login
                return redirect('/login')->with('error', 'Acesso não autorizado. Por favor, faça login.');
            }
        }

        return parent::render($request, $exception);
    }
}