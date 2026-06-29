<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Store types
        $storeTypes = [
            'Daily Needs' => ['Fruits', 'Dairy', 'Beverages', 'Snacks', 'Meat & Seafood', 'Canned', 'Spices', 'Personal Care', 'Cleaning Supplies'],
            'Fresh Bakery' => ['Bread', 'Pastries', 'Cakes', 'Cookies', 'Muffins', 'Buns', 'Pies', 'Bagels'],
            'Pharmacy Essentials' => ['Pain Relief', 'Cold & Cough', 'Vitamins', 'Digestive', 'BP & Heart Disease', 'Skin Care', 'Eye Care', 'Herbal'],
            'Beauty & Cosmetics' => ['Foundations', 'Lipsticks', 'Eyeshadows', 'Mascaras', 'Blushes'],
            'Bag Collections' => ['Handbags', 'Totes', 'Backpacks', 'Wallets', 'Clutches', 'Crossbody'],
            'Clothing & Style' => ['Men', 'Women'],
            'Furniture & Decor' => ['Sofas', 'Chairs', 'Beds', 'Tables', 'Dressers', 'Bookshelves', 'Desks'],
            'Book Collection' => ['Fiction', 'Non-Fiction', 'Sci-Fi', 'Fantasy', 'Biography'],
            'Tech & Gadgets' => ['Phones', 'Tablets', 'Headphones', 'Smart Watches', 'Laptops', 'Cameras'],
            'Pets & Animals Essentials' => ['Dogs', 'Cats', 'Pet Toys', 'Grooming', 'Pet Food'],
            'Fresh Fish' => ['Freshwater', 'Saltwater', 'Aquarium Plants', 'Fish Food', 'Water Care']
        ];

        // Loop through each store type and insert categories
        foreach ($storeTypes as $storeType => $categories) {
            $parent_id = DB::table('product_category')->insertGetId([
                'category_name' => ucfirst(strtolower($storeType)),
                'category_slug' => strtolower($storeType),
                'type' => 'undefined',
                'category_level' => 1,
                'is_featured' => 1,
                'admin_commission_rate' => 10,
                'meta_title' => ucfirst(strtolower($storeType)),
                'meta_description' => ucfirst(strtolower($storeType)),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert subcategories
            foreach ($categories as $category) {
                DB::table('product_category')->insert([
                    'category_name' => $category,
                    'category_slug' => strtolower(str_replace(' ', '-', $category)),
                    'type' => 'undefined',
                    'category_name_paths' => ucfirst(strtolower($storeType)),
                    'parent_path' => strtolower($storeType),
                    'parent_id' => $parent_id,
                    'category_level' => 2,
                    'is_featured' => 1,
                    'admin_commission_rate' => 10,
                    'meta_title' => $category,
                    'meta_description' => $category,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

}
