<?php

namespace Rosalana\Roles\Http\Middleware;

use Closure;
use Rosalana\Roles\Exceptions\InsufficientRoleException;

class HasPermissionIn
{
    /**
     * Handle an incoming request.
     *
     * Checks if the authenticated user has a specific permission in a model bound to a route parameter.
     *
     * Usage: middleware('permission.in:team,edit-posts')
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\JsonResponse)  $next
     * @param  string  $routeParam   The route parameter name that resolves to the model
     * @param  string  $permission   The required permission name
     */
    public function handle($request, Closure $next, string $routeParam, string $permission)
    {
        $user = $request->user();
        $model = $request->route($routeParam);

        if (!$user || !$model || !method_exists($user, 'hasPermission')) {
            throw new InsufficientRoleException();
        }

        if (!$user->hasPermission($permission, $model)) {
            throw new InsufficientRoleException();
        }

        return $next($request);
    }
}
