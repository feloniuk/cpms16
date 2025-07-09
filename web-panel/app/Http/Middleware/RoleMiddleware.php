<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['Ваш аккаунт деактивирован']);
        }

        if (!in_array($user->role, $roles)) {
            abort(403, 'У вас нет доступа к этой странице');
        }

        return $next($request);
    }
}