<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new user
     */
    public function register(array $attributes): User
    {
        return User::create([
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'password' => Hash::make($attributes['password']),
            'role' => $attributes['role'] ?? 'customer',
        ]);
    }

    /**
     * Authenticate user and return token
     */
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('api')->plainTextToken;
        
        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout user by deleting current token
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    /**
     * Logout user from all devices
     */
    public function logoutFromAllDevices(User $user): void
    {
        $user->tokens->each->delete();
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(User $user, string $role): bool
    {
        return $user->role === $role;
    }

    /**
     * Check if user has any of the specified roles
     */
    public function hasAnyRole(User $user, array $roles): bool
    {
        return in_array($user->role, $roles);
    }

    /**
     * Create a provider account
     */
    public function createProvider(array $attributes): User
    {
        return $this->register(array_merge($attributes, ['role' => 'provider']));
    }

    /**
     * Create an admin account
     */
    public function createAdmin(array $attributes): User
    {
        return $this->register(array_merge($attributes, ['role' => 'admin']));
    }
}
