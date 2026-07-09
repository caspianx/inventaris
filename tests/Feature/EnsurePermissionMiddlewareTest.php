<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsurePermission;
use Illuminate\Http\Request;
use Tests\TestCase;

class EnsurePermissionMiddlewareTest extends TestCase
{
    public function test_middleware_allows_when_any_permission_in_comma_separated_list_matches(): void
    {
        $middleware = new EnsurePermission();

        $user = new class {
            public function canAccess(string $permission): bool
            {
                return in_array($permission, ['items.create', 'items.edit'], true);
            }
        };

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = $middleware->handle($request, function ($request) {
            return response('ok', 200);
        }, 'items.create,items.edit');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('ok', $response->getContent());
    }
}
