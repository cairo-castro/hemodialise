<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Checkbox;
use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Validation\ValidationException;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;

class CustomLogin extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        \Log::info('[CustomLogin::authenticate] START', [
            'email' => $this->data['email'] ?? 'not set',
            'remember' => $this->data['remember'] ?? false,
            'session_id' => request()->session()->getId()
        ]);

        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            \Log::warning('[CustomLogin::authenticate] Rate limit exceeded', [
                'email' => $this->data['email'] ?? 'not set'
            ]);

            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        \Log::info('[CustomLogin::authenticate] Validating form data');

        $data = $this->form->getState();

        \Log::info('[CustomLogin::authenticate] Form data validated', [
            'email' => $data['email'],
            'remember' => $data['remember'] ?? false
        ]);

        // Attempt authentication
        \Log::info('[CustomLogin::authenticate] Attempting Laravel Auth::attempt');

        $credentials = [
            'email' => $data['email'],
            'password' => $data['password'],
        ];

        if (!auth()->attempt($credentials, $data['remember'] ?? false)) {
            \Log::warning('[CustomLogin::authenticate] Auth::attempt FAILED', [
                'email' => $data['email']
            ]);

            $this->throwFailureValidationException();
        }

        $user = auth()->user();

        \Log::info('[CustomLogin::authenticate] Auth::attempt SUCCESS', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'session_id' => request()->session()->getId()
        ]);

        // Check if user can access admin panel
        if (!$user->canAccessAdmin()) {
            \Log::warning('[CustomLogin::authenticate] User CANNOT access admin panel', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'unit_id' => $user->unit_id
            ]);

            auth()->logout();

            $this->throwFailureValidationException();
        }

        \Log::info('[CustomLogin::authenticate] User CAN access admin panel', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);

        // Regenerate session
        request()->session()->regenerate();

        \Log::info('[CustomLogin::authenticate] Session regenerated', [
            'new_session_id' => request()->session()->getId()
        ]);

        return app(LoginResponse::class);
    }

    protected function throwFailureValidationException(): never
    {
        \Log::info('[CustomLogin::throwFailureValidationException] Throwing validation exception');

        throw ValidationException::withMessages([
            'data.email' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('email')
                    ->label(__('filament-panels::pages/auth/login.form.email.label'))
                    ->email()
                    ->required()
                    ->autocomplete()
                    ->autofocus()
                    ->extraInputAttributes(['tabindex' => 1]),
                TextInput::make('password')
                    ->label(__('filament-panels::pages/auth/login.form.password.label'))
                    ->password()
                    ->required()
                    ->extraInputAttributes(['tabindex' => 2]),
                Checkbox::make('remember')
                    ->label(__('filament-panels::pages/auth/login.form.remember.label')),
            ]);
    }
}
