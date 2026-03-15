<?php

namespace Rosalana\Roles\Http\Middleware;

use Closure;
use Rosalana\Roles\Exceptions\InsufficientRoleException;

class IsAtLeast
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\JsonResponse)  $next
     * @param  string  $role
     */
    public function handle($request, Closure $next, string $role)
    {
        $user = $request->user();

        if (!$user || !method_exists($user, 'role') || !$user->role()?->isAtLeast($role)) {
            throw new InsufficientRoleException();
        }

        return $next($request);
    }
}