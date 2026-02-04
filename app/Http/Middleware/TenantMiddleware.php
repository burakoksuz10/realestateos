<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get tenant from subdomain or header
        $tenant = $this->resolveTenant($request);
        
        if ($tenant) {
            // Set tenant context
            app()->instance('current_tenant', $tenant);
            
            // Apply tenant scopes to models
            $this->applyTenantScopes($tenant);
        }
        
        return $next($request);
    }

    /**
     * Resolve tenant from request
     */
    protected function resolveTenant(Request $request)
    {
        // Try to get from subdomain
        $host = $request->getHost();
        $parts = explode('.', $host);
        
        if (count($parts) > 2) {
            $subdomain = $parts[0];
            return \Modules\Core\Models\Tenant::where('subdomain', $subdomain)->first();
        }
        
        // Try to get from header (for API requests)
        if ($tenantId = $request->header('X-Tenant-ID')) {
            return \Modules\Core\Models\Tenant::find($tenantId);
        }
        
        // Try to get from authenticated user
        if ($user = $request->user()) {
            return $user->tenant;
        }
        
        return null;
    }

    /**
     * Apply tenant scopes to models
     */
    protected function applyTenantScopes($tenant)
    {
        // This will be handled by model traits
    }
}
