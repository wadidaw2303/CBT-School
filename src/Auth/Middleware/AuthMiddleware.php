<?php

declare(strict_types=1);

namespace Auth\Middleware;

use Core\Request;
use Core\Response;
use Auth\Auth;

class AuthMiddleware
{
    public function handle(Request $request): ?Response
    {
        if (Auth::getInstance()->guest()) {
            if ($request->expectsJson()) {
                return Response::unauthorized('Unauthenticated.');
            }

            Session::flash('error', 'Silakan login terlebih dahulu.');
            return Response::redirect(url('login'));
        }

        return null;
    }
}