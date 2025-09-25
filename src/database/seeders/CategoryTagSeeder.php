<?php

namespace Database\Seeders;

use App\Domain\Article\Models\Category;
use App\Domain\Article\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoryTagSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Passports', 'description' => 'Passport related guides and updates'],
            ['name' => 'Visas', 'description' => 'Visa processes and checklists'],
            ['name' => 'Appointments', 'description' => 'Scheduling and office locations'],
        ];

        foreach ($categories as $c) {
            Category::updateOrCreate(
                ['slug' => Str::slug($c['name'])],
                ['name' => $c['name'], 'description' => $c['description'], 'is_active' => true]
            );
        }

        $tags = ['Guides', 'Checklist', 'News', 'How-To', 'Updates'];
        foreach ($tags as $t) {
            Tag::updateOrCreate(
                ['slug' => Str::slug($t)],
                ['name' => $t, 'description' => null, 'is_active' => true]
            );
        }
    }
}

