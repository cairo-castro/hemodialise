<?php

namespace App\Http\Middleware;

use Filament\Http\Middleware\Authenticate as FilamentAuthenticate;
use Illuminate\Http\Request;
use Closure;

class FilamentAuthenticateWithLogging extends FilamentAuthenticate
{
    public function handle($request, Closure $next, ...$guards): mixed
    {
        \Log::info('[FilamentAuthenticate] START', [
            'url' => $request->url(),
            'path' => $request->path(),
            'method' => $request->method(),
            'route_name' => $request->route() ? $request->route()->getName() : null,
            'is_authenticated' => auth()->check(),
            'guards' => $guards,
            'session_id' => $request->session()->getId(),
            'cookies' => array_keys($request->cookies->all())
        ]);

        try {
            $response = parent::handle($request, $next, ...$guards);

            \Log::info('[FilamentAuthenticate] Authentication passed', [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()?->email,
                'response_status' => $response->getStatusCode(),
                'is_redirect' => $response->isRedirect(),
                'redirect_url' => $response->isRedirect() ? $response->headers->get('Location') : null
            ]);

            return $response;
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            \Log::warning('[FilamentAuthenticate] Authentication FAILED - redirecting to login', [
                'exception' => $e->getMessage(),
                'guards' => $e->guards(),
                'redirect_to' => $e->redirectTo($request) ?? 'default login page'
            ]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('[FilamentAuthenticate] Unexpected error', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function redirectTo($request): ?string
    {
        $redirectTo = parent::redirectTo($request);

        \Log::info('[FilamentAuthenticate] Determining redirect', [
            'redirect_to' => $redirectTo,
            'request_url' => $request->url()
        ]);

        return $redirectTo;
    }
}
