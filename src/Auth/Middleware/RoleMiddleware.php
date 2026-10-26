<?php

declare(strict_types=1);

namespace Auth\Middleware;

use Core\Request;
use Core\Response;
use Auth\Auth;

class RoleMiddleware
{
    public function handle(Request $request, string $role): ?Response
    {
        $user = Auth::getInstance()->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return Response::unauthorized('Unauthenticated.');
            }
            Session::flash('error', 'Silakan login terlebih dahulu.');
            return Response::redirect(url('login'));
        }

        if (!$user->hasRole($role)) {
            if ($request->expectsJson()) {
                return Response::forbidden('Anda tidak memiliki akses ke halaman ini.');
            }
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return null;
    }
}