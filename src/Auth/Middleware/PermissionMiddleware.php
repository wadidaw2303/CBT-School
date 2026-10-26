<?php

declare(strict_types=1);

namespace Auth\Middleware;

use Core\Request;
use Core\Response;
use Auth\Auth;

class PermissionMiddleware
{
    public function handle(Request $request, string $permission): ?Response
    {
        $user = Auth::getInstance()->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return Response::unauthorized('Unauthenticated.');
            }
            Session::flash('error', 'Silakan login terlebih dahulu.');
            return Response::redirect(url('login'));
        }

        if (!$user->can($permission)) {
            if ($request->expectsJson()) {
                return Response::forbidden('Anda tidak memiliki izin untuk melakukan aksi ini.');
            }
            abort(403, 'Anda tidak memiliki izin untuk melakukan aksi ini.');
        }

        return null;
    }
}