<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Homeware', 'Kitchen', 'Care'] as $name) {
            $category = Category::firstOrCreate(['slug' => \Illuminate\Support\Str::slug($name)], ['name' => $name]);
            Product::factory()->count(4)->create(['category_id' => $category->id]);
        }
    }
}
