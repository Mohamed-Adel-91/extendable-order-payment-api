<?php

namespace App\Services;

use App\Models\Admin;
use Illuminate\Support\Facades\Auth;

class AdminAuthService
{

    public function login(array $data): ?array
    {
        $blocked = Admin::withTrashed()
            ->where('username', $data['username'])
            ->whereNotNull('deleted_at')
            ->exists();

        if ($blocked) {
            return null;
        }

        $token = Auth::guard('admin_api')->attempt([
            'username' => $data['username'],
            'password' => $data['password'],
        ]);

        if (!$token) {
            return null;
        }

        $admin = Auth::guard('admin_api')->user();

        if (!$admin || (method_exists($admin, 'trashed') && $admin->trashed())) {
            Auth::guard('admin_api')->logout();
            return null;
        }

        return $this->tokenPayload($token, $admin);
    }

    public function logout(): void
    {
        Auth::guard('admin_api')->logout();
    }

    public function me(): ?array
    {
        $admin = Auth::guard('admin_api')->user();

        if (!$admin) {
            return null;
        }

        if (method_exists($admin, 'trashed') && $admin->trashed()) {
            Auth::guard('admin_api')->logout();
            return null;
        }

        return ['admin' => $admin];
    }

    private function tokenPayload(string $token, Admin $admin): array
    {
        $ttlMinutes = Auth::guard('admin_api')->factory()->getTTL();

        return [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => $ttlMinutes * 60,
            'admin'        => $admin,
        ];
    }
}
