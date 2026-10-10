<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Inicio de sesión del panel en dos secciones: marca a la izquierda y formulario a la derecha.
 */
class Login extends BaseLogin
{
    protected static string $layout = 'filament.layouts.login-split';

    /** El logo ya está en el panel de marca. */
    public function hasLogo(): bool
    {
        return false;
    }

    public function getHeading(): string|Htmlable|null
    {
        return filled($this->userUndertakingMultiFactorAuthentication) ? parent::getHeading() : 'Bienvenido de nuevo';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return filled($this->userUndertakingMultiFactorAuthentication)
            ? parent::getSubheading()
            : 'Ingresa a tu panel para administrar el catálogo.';
    }
}
