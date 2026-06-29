<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('banners')->insert([
            [
                "user_id" => 1,
                "store_id" => 1,
                "title" => "New Year Sale",
                "description" => "Enjoy amazing discounts on our top products!",
                "background_image" => "https://example.com/images/background.jpg",
                "thumbnail_image" => "https://example.com/images/thumbnail.jpg",
                "button_text" => "Shop Now",
                "button_color" => "#ff5733",
                "redirect_url" => "https://example.com/sale",
                "location" => "home_page",
                "type" => "Banner-1",
                "status" => true
            ],
            [
                "user_id" => 1,
                "store_id" => 1,
                "title" => "New Year Sale",
                "description" => "Enjoy amazing discounts on our top products!",
                "background_image" => "https://example.com/images/background.jpg",
                "thumbnail_image" => "https://example.com/images/thumbnail.jpg",
                "button_text" => "Shop Now",
                "button_color" => "#ff5733",
                "redirect_url" => "https://example.com/sale",
                "location" => "home_page",
                "type" => "Banner-1",
                "status" => true
            ],
            [
                "user_id" => 1,
                "store_id" => 1,
                "title" => "New Year Sale",
                "description" => "Enjoy amazing discounts on our top products!",
                "background_image" => "https://example.com/images/background.jpg",
                "thumbnail_image" => "https://example.com/images/thumbnail.jpg",
                "button_text" => "Shop Now",
                "button_color" => "#ff5733",
                "redirect_url" => "https://example.com/sale",
                "location" => "home_page",
                "type" => "Banner-1",
                "status" => true
            ],
        ]);
    }
}
