<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = User::where('role', 'provider')->get();
        $categories = Category::all();

        // Predefined services for each provider
        $serviceTemplates = [
            // Beauty & Wellness
            [
                'category' => 'Beauty & Wellness',
                'services' => [
                    ['name' => 'Haircut & Styling', 'description' => 'Professional haircut and styling service', 'duration' => 60, 'price' => 45.00],
                    ['name' => 'Hair Color & Highlights', 'description' => 'Professional hair coloring and highlighting', 'duration' => 120, 'price' => 85.00],
                    ['name' => 'Manicure', 'description' => 'Complete nail care and polish', 'duration' => 45, 'price' => 25.00],
                    ['name' => 'Pedicure', 'description' => 'Foot care and nail treatment', 'duration' => 60, 'price' => 35.00],
                    ['name' => 'Facial Treatment', 'description' => 'Relaxing facial cleansing and treatment', 'duration' => 75, 'price' => 55.00],
                ]
            ],
            // Health & Medical
            [
                'category' => 'Health & Medical',
                'services' => [
                    ['name' => 'General Consultation', 'description' => 'General health consultation', 'duration' => 30, 'price' => 75.00],
                    ['name' => 'Physical Therapy', 'description' => 'Rehabilitation and therapy session', 'duration' => 60, 'price' => 95.00],
                    ['name' => 'Dental Cleaning', 'description' => 'Professional dental cleaning', 'duration' => 45, 'price' => 120.00],
                    ['name' => 'Eye Examination', 'description' => 'Comprehensive eye health check', 'duration' => 45, 'price' => 85.00],
                ]
            ],
            // Fitness & Sports
            [
                'category' => 'Fitness & Sports',
                'services' => [
                    ['name' => 'Personal Training', 'description' => 'One-on-one fitness training session', 'duration' => 60, 'price' => 65.00],
                    ['name' => 'Yoga Class', 'description' => 'Private yoga instruction', 'duration' => 75, 'price' => 45.00],
                    ['name' => 'Sports Massage', 'description' => 'Therapeutic sports massage', 'duration' => 60, 'price' => 80.00],
                    ['name' => 'Nutrition Consultation', 'description' => 'Personalized nutrition planning', 'duration' => 45, 'price' => 55.00],
                ]
            ],
            // Education & Tutoring
            [
                'category' => 'Education & Tutoring',
                'services' => [
                    ['name' => 'Math Tutoring', 'description' => 'Private mathematics tutoring', 'duration' => 60, 'price' => 40.00],
                    ['name' => 'Language Lessons', 'description' => 'Foreign language instruction', 'duration' => 60, 'price' => 50.00],
                    ['name' => 'Music Lessons', 'description' => 'Private music instrument lessons', 'duration' => 45, 'price' => 45.00],
                    ['name' => 'Test Preparation', 'description' => 'Standardized test preparation', 'duration' => 90, 'price' => 70.00],
                ]
            ],
            // Home & Maintenance
            [
                'category' => 'Home & Maintenance',
                'services' => [
                    ['name' => 'House Cleaning', 'description' => 'Professional house cleaning service', 'duration' => 120, 'price' => 90.00],
                    ['name' => 'Plumbing Repair', 'description' => 'Basic plumbing repairs and maintenance', 'duration' => 90, 'price' => 85.00],
                    ['name' => 'Electrical Work', 'description' => 'Electrical installation and repair', 'duration' => 90, 'price' => 95.00],
                    ['name' => 'Garden Maintenance', 'description' => 'Lawn care and garden maintenance', 'duration' => 120, 'price' => 75.00],
                ]
            ],
        ];

        foreach ($providers as $provider) {
            // Each provider gets 2-4 services from different categories
            $providerServices = collect($serviceTemplates)->random(rand(2, 4));
            
            foreach ($providerServices as $categoryData) {
                $category = $categories->firstWhere('name', $categoryData['category']);
                
                // Pick 1-3 services from this category
                $servicesToCreate = collect($categoryData['services'])->random(rand(1, 3));
                
                foreach ($servicesToCreate as $serviceData) {
                    Service::create([
                        'provider_id' => $provider->id,
                        'category_id' => $category->id,
                        'name' => $serviceData['name'],
                        'description' => $serviceData['description'],
                        'duration' => $serviceData['duration'],
                        'price' => $serviceData['price'],
                        'is_published' => rand(0, 10) > 2, // 80% chance of being published
                    ]);
                }
            }
        }

        // Create some specific test services for API testing
        $testProvider = User::where('email', 'test.provider@example.com')->first();
        $beautyCategory = $categories->firstWhere('name', 'Beauty & Wellness');
        
        if ($testProvider && $beautyCategory) {
            Service::create([
                'provider_id' => $testProvider->id,
                'category_id' => $beautyCategory->id,
                'name' => 'Test Service - Haircut',
                'description' => 'This is a test service for API testing',
                'duration' => 30,
                'price' => 25.00,
                'is_published' => true,
            ]);

            Service::create([
                'provider_id' => $testProvider->id,
                'category_id' => $beautyCategory->id,
                'name' => 'Test Service - Massage',
                'description' => 'Another test service for comprehensive testing',
                'duration' => 60,
                'price' => 75.00,
                'is_published' => true,
            ]);
        }
    }
}