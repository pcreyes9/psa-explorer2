<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\Component;
use Filament\Forms\Components\TextInput;

class Login extends BaseLogin
{
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('username')
            ->label('Username')
            ->required()
            ->autocomplete('username')
            ->autofocus();
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'username' => $data['username'],
            'password' => $data['password'],
            'is_active' => true,
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'data.username' => 'These credentials do not match our records.',
        ]);
    }
}