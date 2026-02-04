<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Modules\Core\Models\AuditLog;

class AuditMiddleware
{
    /**
     * Routes that should be audited
     */
    protected array $auditableRoutes = [
        'admin/*',
        'api/*',
    ];

    /**
     * Routes that should be excluded from auditing
     */
    protected array $excludedRoutes = [
        'api/health',
        'api/ping',
        'admin/notifications/check',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only audit if user is authenticated and route is auditable
        if ($this->shouldAudit($request)) {
            $this->logRequest($request, $response);
        }

        return $response;
    }

    /**
     * Determine if the request should be audited
     */
    protected function shouldAudit(Request $request): bool
    {
        if (!$request->user()) {
            return false;
        }

        $path = $request->path();

        // Check if route is excluded
        foreach ($this->excludedRoutes as $pattern) {
            if ($request->is($pattern)) {
                return false;
            }
        }

        // Check if route is auditable
        foreach ($this->auditableRoutes as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Log the request
     */
    protected function logRequest(Request $request, Response $response): void
    {
        try {
            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => $request->method(),
                'resource' => $request->path(),
                'resource_id' => $this->extractResourceId($request),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_data' => $this->sanitizeData($request->except(['password', 'password_confirmation', 'token'])),
                'response_code' => $response->getStatusCode(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Silently fail - don't break the request
            \Log::error('Audit log failed: ' . $e->getMessage());
        }
    }

    /**
     * Extract resource ID from request
     */
    protected function extractResourceId(Request $request): ?string
    {
        $segments = $request->segments();
        
        // Look for numeric ID in URL segments
        foreach (array_reverse($segments) as $segment) {
            if (is_numeric($segment)) {
                return $segment;
            }
        }

        // Check route parameters
        foreach ($request->route()?->parameters() ?? [] as $key => $value) {
            if (is_numeric($value) || (is_string($value) && strlen($value) === 36)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Sanitize sensitive data
     */
    protected function sanitizeData(array $data): array
    {
        $sensitiveKeys = ['password', 'token', 'secret', 'api_key', 'credit_card'];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->sanitizeData($value);
            } elseif (in_array(strtolower($key), $sensitiveKeys)) {
                $data[$key] = '[REDACTED]';
            }
        }

        return $data;
    }
}
