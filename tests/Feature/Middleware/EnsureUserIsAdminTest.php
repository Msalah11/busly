<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

describe('EnsureUserIsAdmin Middleware', function (): void {
    it('allows admin users to pass through middleware', function (): void {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $request = Request::create('/admin/test');
        $request->setUserResolver(fn () => $admin);

        $middleware = new EnsureUserIsAdmin;
        $response = $middleware->handle($request, fn (): \Symfony\Component\HttpFoundation\Response => new Response('success', 200));

        expect($response->getStatusCode())->toBe(200);
        expect($response->getContent())->toBe('success');
    });

    it('denies regular users access through middleware', function (): void {
        $user = User::factory()->create(['role' => Role::USER]);
        $request = Request::create('/admin/test');
        $request->setUserResolver(fn () => $user);

        $middleware = new EnsureUserIsAdmin;

        expect(fn (): \Symfony\Component\HttpFoundation\Response => $middleware->handle($request, fn (): \Symfony\Component\HttpFoundation\Response => new Response('success', 200)))
            ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
    });

    it('denies unauthenticated users access through middleware', function (): void {
        $request = Request::create('/admin/test');
        $request->setUserResolver(fn (): null => null);

        $middleware = new EnsureUserIsAdmin;

        expect(fn (): \Symfony\Component\HttpFoundation\Response => $middleware->handle($request, fn (): \Symfony\Component\HttpFoundation\Response => new Response('success', 200)))
            ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
    });

    it('middleware correctly integrates with Laravel auth system', function (): void {
        // Test that the middleware properly integrates with Laravel's auth system
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create(['role' => Role::USER]);

        // Admin should have access
        expect($admin->isAdmin())->toBeTrue();
        expect($user->isAdmin())->toBeFalse();

        // Verify the middleware logic directly
        $middleware = new EnsureUserIsAdmin;
        $adminRequest = Request::create('/test');
        $adminRequest->setUserResolver(fn () => $admin);

        $userRequest = Request::create('/test');
        $userRequest->setUserResolver(fn () => $user);

        // Admin request should pass
        $response = $middleware->handle($adminRequest, fn (): \Symfony\Component\HttpFoundation\Response => new Response('success'));
        expect($response->getContent())->toBe('success');

        // User request should fail
        expect(fn (): \Symfony\Component\HttpFoundation\Response => $middleware->handle($userRequest, fn (): \Symfony\Component\HttpFoundation\Response => new Response('success')))
            ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
    });

    it('blocks non-admin users from protected routes', function (): void {
        $user = User::factory()->create(['role' => Role::USER]);

        $this->actingAs($user)
            ->get('/admin/dashboard')
            ->assertStatus(403);
    });

    it('redirects unauthenticated users to login', function (): void {
        $this->get('/admin/dashboard')
            ->assertRedirect('/login');
    });
});
