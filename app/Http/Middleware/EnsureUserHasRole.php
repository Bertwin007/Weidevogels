<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * @param  array<int, UserRole|string>  $roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        foreach ($roles as $role) {
            $expected = $role instanceof UserRole ? $role : UserRole::from($role);

            if ($user->role === $expected || ($expected === UserRole::Annotator && $user->isAdmin())) {
                return $next($request);
            }
        }

        abort(403);
    }
}
