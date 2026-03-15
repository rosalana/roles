<?php

namespace Rosalana\Roles\Http\Middleware;

use Closure;
use Rosalana\Roles\Exceptions\InsufficientRoleException;

class HasRoleIn
{
    /**
     * Handle an incoming request.
     *
     * Checks if the authenticated user has a specific role in a model bound to a route parameter.
     *
     * Usage: middleware('role.in:team,owner')
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\JsonResponse)  $next
     * @param  string  $routeParam  The route parameter name that resolves to the model
     * @param  string  $role        The required role name
     */
    public function handle($request, Closure $next, string $routeParam, string $role)
    {
        $user = $request->user();
        $model = $request->route($routeParam);

        if (!$user || !$model || !method_exists($user, 'roleIn')) {
            throw new InsufficientRoleException();
        }

        $userRole = $user->roleIn($model);

        if (!$userRole || $userRole->name !== $role) {
            throw new InsufficientRoleException();
        }

        return $next($request);
    }
}
