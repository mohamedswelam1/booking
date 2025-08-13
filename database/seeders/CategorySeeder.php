<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Beauty & Wellness',
                'slug' => 'beauty-wellness',
            ],
            [
                'name' => 'Health & Medical',
                'slug' => 'health-medical',
            ],
            [
                'name' => 'Fitness & Sports',
                'slug' => 'fitness-sports',
            ],
            [
                'name' => 'Education & Tutoring',
                'slug' => 'education-tutoring',
            ],
            [
                'name' => 'Home & Maintenance',
                'slug' => 'home-maintenance',
            ],
            [
                'name' => 'Business & Consulting',
                'slug' => 'business-consulting',
            ],
            [
                'name' => 'Technology & IT',
                'slug' => 'technology-it',
            ],
            [
                'name' => 'Automotive',
                'slug' => 'automotive',
            ],
            [
                'name' => 'Pet Care',
                'slug' => 'pet-care',
            ],
            [
                'name' => 'Entertainment',
                'slug' => 'entertainment',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}