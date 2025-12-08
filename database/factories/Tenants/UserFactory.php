<?php

namespace Database\Factories\Tenants;

use Illuminate\Support\Str;
use App\Models\Tenants\User;
use Illuminate\Support\Facades\Hash;
use App\Services\UserNotificationPreferenceService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenants\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'can_login' => false,
            'email' => fake()->unique()->safeEmail(),
            // 'email_verified_at' => now(),
            // 'password' => static::$password ??= Hash::make('password'),
            // 'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function withRole($role)
    {
        return $this->afterCreating(function (User $user) use ($role) {
            $user->update(['can_login' => true]);
            $user->assignRole($role);
            if ($user->hasAnyRole(['Admin', 'Maintenance Manager']))
                app(UserNotificationPreferenceService::class)->createDefaultUserNotificationPreferences($user);
        });
    }
}
