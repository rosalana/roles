<?php

namespace Rosalana\Roles\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Rosalana\Roles\Exceptions\AccountSuspendedException;

class EnsureUserIsNotSuspended
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\JsonResponse)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && method_exists($user, 'isSuspended') && $user->isSuspended()) {
            throw new AccountSuspendedException();
        }

        return $next($request);
    }
}
