<?php

declare(strict_types=1);

namespace Auth\Middleware;

use Core\Request;
use Core\Response;
use Auth\Auth;

class GuestMiddleware
{
    public function handle(Request $request): ?Response
    {
        if (Auth::getInstance()->check()) {
            $user = Auth::getInstance()->user();
            $role = $user?->getRole()?->slug;

            return match ($role) {
                'admin' => Response::redirect(url('admin/dashboard')),
                'teacher' => Response::redirect(url('teacher/dashboard')),
                'student' => Response::redirect(url('student/dashboard')),
                default => Response::redirect(url('/')),
            };
        }

        return null;
    }
}