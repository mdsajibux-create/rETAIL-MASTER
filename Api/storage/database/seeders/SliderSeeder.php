<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SliderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('sliders')->insert([
            [
                'title' => 'Sample Title 1',
                'sub_title' => 'Sample Subtitle 1',
                'description' => 'This is a description for the first record.',
                'image' => '3',
                'button_text' => 'Click Here',
                'button_url' => 'https://example.com/button1',
                'redirect_url' => 'https://example.com/redirect1',
                'order' => 1,
                'status' => 1, // Active
                'created_by' => 8,
                'updated_by' => 8,
            ],
            [
                'title' => 'Sample Title 2',
                'sub_title' => 'Sample Subtitle 2',
                'description' => 'This is a description for the second record.',
                'image' => '3',
                'button_text' => 'Learn More',
                'button_url' => 'https://example.com/button2',
                'redirect_url' => 'https://example.com/redirect2',
                'order' => 2,
                'status' => 0, // Inactive
                'created_by' => 8,
                'updated_by' => 8,
            ],
        ]);

    }
}
