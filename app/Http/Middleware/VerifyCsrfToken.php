<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
  'ubhn/acc/*',
        'ubhn/tolak/*',
        'ubhn/acc2/*',
        'ubhn/tolak2/*',       //
    ];
}
