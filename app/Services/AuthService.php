<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data): array
    {
        $user = User::query()->create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = Auth::guard('api')->attempt([
            'email'    => $data['email'],
            'password' => $data['password'],
        ]);

        return $this->tokenPayload($token, $user);
    }

    public function login(array $data): ?array
    {
        $blocked = User::withTrashed()
            ->where('email', $data['email'])
            ->whereNotNull('deleted_at')
            ->exists();

        if ($blocked) {
            return null;
        }

        $token = Auth::guard('api')->attempt([
            'email'    => $data['email'],
            'password' => $data['password'],
        ]);

        if (!$token) {
            return null;
        }

        $user = Auth::guard('api')->user();

        if (!$user || (method_exists($user, 'trashed') && $user->trashed())) {
            Auth::guard('api')->logout();
            return null;
        }

        return $this->tokenPayload($token, $user);
    }

    public function logout(): void
    {
        Auth::guard('api')->logout();
    }

    public function me(): ?array
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return null;
        }

        if (method_exists($user, 'trashed') && $user->trashed()) {
            Auth::guard('api')->logout();
            return null;
        }

        return ['user' => $user];
    }

    private function tokenPayload(string $token, User $user): array
    {
        $ttlMinutes = Auth::guard('api')->factory()->getTTL();

        return [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => $ttlMinutes * 60,
            'user'         => $user,
        ];
    }
}
