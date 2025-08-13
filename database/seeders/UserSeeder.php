<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@booking.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Service Providers
        $providers = [
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah@provider.com',
                'password' => Hash::make('password123'),
                'role' => 'provider',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Mike Chen',
                'email' => 'mike@provider.com',
                'password' => Hash::make('password123'),
                'role' => 'provider',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Lisa Rodriguez',
                'email' => 'lisa@provider.com',
                'password' => Hash::make('password123'),
                'role' => 'provider',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'David Wilson',
                'email' => 'david@provider.com',
                'password' => Hash::make('password123'),
                'role' => 'provider',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Emma Thompson',
                'email' => 'emma@provider.com',
                'password' => Hash::make('password123'),
                'role' => 'provider',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($providers as $provider) {
            User::create($provider);
        }

        // Customers
        $customers = [
            [
                'name' => 'John Smith',
                'email' => 'john@customer.com',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Emily Davis',
                'email' => 'emily@customer.com',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Robert Brown',
                'email' => 'robert@customer.com',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Jessica Miller',
                'email' => 'jessica@customer.com',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Alex Turner',
                'email' => 'alex@customer.com',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Maria Garcia',
                'email' => 'maria@customer.com',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($customers as $customer) {
            User::create($customer);
        }

        // Additional test users for development
        User::create([
            'name' => 'Test Provider',
            'email' => 'test.provider@example.com',
            'password' => Hash::make('password123'),
            'role' => 'provider',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Test Customer',
            'email' => 'test.customer@example.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);
    }
}