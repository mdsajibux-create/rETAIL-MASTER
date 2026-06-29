<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class
BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
//        // List of brand names to insert
//        $brands = [
//            'ShopEase', 'QuickCart', 'GreenBasket', 'FreshBite', 'BookHaven', 'UrbanCarry',
//            'LeatherLux', 'CarryAll', 'StyleBag', 'TravelTote', 'BagBuddy', 'ChicTote',
//            'LuxeCarry', 'Bagtopia', 'TrendTotes', 'TechVerse', 'GadgetFlow', 'InnovateTech',
//            'SmartGear', 'DigiHub', 'TechSavant', 'FutureTech', 'GadgetWave', 'NextGenGears',
//            'SmartFusion', 'DreamDecor', 'CozyHome', 'HomeHaven', 'LuxeLiving', 'ElegantHomes',
//            'PureSpaces', 'UrbanNest', 'StyleHaven', 'ComfortZone', 'BrightSpaces', 'StyleWave',
//            'TrendAura', 'ChicVibe', 'UrbanLook', 'Trendique', 'ModishWear', 'ClassyCouture',
//            'VogueMoods', 'LuxeApparel', 'FashionXpert', 'FitFusion', 'SportPro', 'ActiveZone',
//            'GymVibe', 'PowerFit', 'EnduranceX', 'MoveStrong', 'SportaLife', 'FitFuel',
//            'PeakFitness', 'PureRadiance', 'GlowSkin', 'VitalBloom', 'LuxeBeauty', 'SkinEssence',
//            'FreshGlow', 'HealthAura', 'VitalGlo', 'PureCare', 'GlowLabs', 'ToyWorld', 'KidzPlay',
//            'FunTimeToys', 'HappyTots', 'KiddoLand', 'ToyNest', 'WonderTots', 'TinyAdventures',
//            'PlayHub', 'ToyJoy', 'SnapShop', 'PrimeGoods', 'DailyDeals', 'SmartBuy', 'FreshFinds',
//            'MegaMart', 'ShopCart', 'MarketXpress', 'NextDayShop', 'GroceryExpress', 'BuyDirect',
//            'EcoShopper', 'OneStopMart', 'CityStore', 'OnlineChoice', 'FreshDirect', 'BuyNowMarket',
//            'ShopMaster', 'ShopToday', 'FastMart', 'QuickBuy', 'DealHub', 'DirectStore', 'SuperShop',
//            'EcomHub', 'SmartMarket', 'MegaDeals', 'ShoppingEase', 'OnlineBargains', 'OneClickShop',
//            'BuyQuickly', 'GlobalMarket', 'EStoreX', 'SuperDeals', 'CartKing', 'DealStack', 'ShopNGo',
//            'BargainCorner', 'GreatDeals', 'ShopNow', 'MarketMaster', 'EcommercePro', 'QuickPickStore',
//            'CartMania', 'OnlineShopper', 'GlobalDeals', 'BigCart', 'ExpressShop', 'MarketX', 'ShopFiesta'
//        ];
//
//        // Loop through the brands and insert each one into the database
//        foreach ($brands as $index => $brand_name) {
//            DB::table('product_brand')->insert([
//                'brand_name' => $brand_name,
//                'brand_slug' => strtolower(str_replace(' ', '-', $brand_name)), // Slugify the brand name
//                'brand_logo' => '1', // Assuming you have a default logo ID (change this accordingly)
//                'meta_title' => 'Meta Title for ' . $brand_name,
//                'meta_description' => 'Meta description for ' . $brand_name,
//                'seller_relation_with_brand' => 'Seller relation description for ' . $brand_name,
//                'authorization_valid_from' => now(),
//                'authorization_valid_to' => now()->addYear(),
//                'display_order' => $index + 1,  // Ordering the brands
//                'created_by' => 1,  // Assuming user ID of the admin who created the brand
//                'updated_by' => 1,  // Same as created_by for initial insert
//                'status' => 1,  // Assuming status '1' means active
//                'created_at' => now(),
//                'updated_at' => now(),
//            ]);
//        }
    }
}
