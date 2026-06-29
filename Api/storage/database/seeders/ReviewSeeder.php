<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reviews = [];

        for ($i = 1; $i <= 100; $i++) {
            $isProduct = rand(0, 1); // 50% chance for product or user
            $reviews[] = [
                'order_id' => rand(1, 50),
                'store_id' => rand(1, 2),
                'reviewable_id' => $isProduct ? rand(1, 100) : 6,
                'reviewable_type' => $isProduct ? 'Modules\\Product\\app\\Models\\Product' : 'App\\Models\\User',
                'customer_id' => rand(1, 50),
                'review' => Str::random(50),
                'rating' => rand(10, 50) / 10, // Random rating between 1.0 and 5.0
                'status' => ['pending', 'approved', 'rejected'][rand(0, 2)],
                'like_count' => rand(0, 100),
                'dislike_count' => rand(0, 50),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('reviews')->insert($reviews);
    }
}
