<?php

namespace Database\Seeders;

use App\Enums\Behaviour;
use App\Enums\ProductType;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Modules\Branch\app\Models\Branch;
use Modules\Catalog\app\Models\ProductBrand;
use Modules\Catalog\app\Models\ProductCategory;
use Modules\Catalog\app\Models\Unit;
use Modules\Product\app\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch store IDs from the stores table
        $stores = Branch::pluck('id')->toArray(); // Get store IDs as an array
        // You can customize this to use actual categories, brands, etc.
        $categories = ProductCategory::pluck('id')->toArray();
        $brands = ProductBrand::pluck('id')->toArray();
        $units = Unit::pluck('id')->toArray();  // Converts the collection to an array
        $behaviours = Behaviour::cases();
        $types = ProductType::cases();  // Get all the cases of StoreType
        $randomType = $types[array_rand($types)]->value;  // Get a random enum value

        // Example attribute sets for different types
        $attributes_sets = [
            'grocery' => [
                'Weight' => ['50g','100g', '150g','250g', '500g', '1kg', '1.5kg', '2kg','3kg', '4kg', '5kg'],
                'Type' => ['Fresh', 'Frozen', 'Dried','Smoked', 'Marinated','Liquid', 'Powder', 'Spray', 'Wipes'],
                'Flavor' => ['Spicy', 'Sweet', 'Salty', 'Cheesy','Honey', 'Chocolate', 'Vanilla', 'Fruits'],
                'Packaging' => ['Single Pack', 'Multi-Pack','Loose', 'Packed', 'Resealable Bag','Bottle', 'Carton', 'Plastic Tub','Box', 'Bag'],
                'Packaging Size' => ['Small', 'Medium', 'Large','500ml', '1L', '5L', '1 Piece','2 Pieces','4 Pieces','6 Pieces','12 Pieces'],
                'Expiry Date' => [ '2025-12-31', '2026-06-30', '2027-01-01', '2027-12-31', '2028-06-30', '2028-12-31', '2029-01-15', '2029-06-30', '2029-12-31', '2030-06-30','2030-12-31',],
            ],
            'bakery' => [
                'Flavor' => ['Vanilla', 'Chocolate', 'Strawberry'],
                'Weight' => ['500g', '1kg', '2kg'],
                'Packaging Type' => ['Box', 'Bag', 'Plastic'],
                'Expiry Date' => ['2025-12-31', '2026-06-30', '2027-01-01', '2027-12-31', '2028-06-30', '2028-12-31', '2029-01-15', '2029-06-30', '2029-12-31', '2030-06-30', '2030-12-31',],
            ],
            'medicine' => [
                'Dosage' => ['50mg', '100mg', '200mg'],
                'Manufacturer' => ['Company A', 'Company B'],
                'Type' => ['Tablet', 'Capsule', 'Syrup', 'Injection'],
                'Expiry Date' => ['2025-12-31', '2026-06-30', '2027-01-01', '2027-12-31', '2028-06-30', '2028-12-31', '2029-01-15', '2029-06-30', '2029-12-31', '2030-06-30', '2030-12-31',],
            ],
            'makeup' => [
                'Shade' => ['Light', 'Medium', 'Dark', 'Fair', 'Tan', 'Deep'],
                'Volume' => ['15ml', '30ml', '50ml', '100ml'],
                'Skin Type' => ['Oily', 'Dry', 'Combination', 'Sensitive', 'Normal'],
                'Product Type' => ['Foundation', 'Concealer', 'Lipstick', 'Mascara', 'Eyeliner', 'Blush', 'Highlighter'],
                'Packaging' => ['Tube', 'Bottle', 'Compact', 'Palette'],
                'Expiry Date' => ['2025-12-31', '2026-06-30', '2027-01-01', '2027-12-31', '2028-06-30', '2028-12-31', '2029-01-15', '2029-06-30', '2029-12-31', '2030-06-30', '2030-12-31',],
            ],
            'clothing' => [
                'Color' => [
                    'Red', 'Blue', 'Green', 'Black', 'White', 'Yellow', 'Pink', 'Purple', 'Orange', 'Gray', 'Brown', 'Beige', 'Navy', 'Turquoise', 'Indigo'
                ],
                'Size' => [
                    'XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL', 'XXS', 'One Size'
                ],
                'Material' => [
                    'Cotton', 'Leather', 'Polyester', 'Linen', 'Silk', 'Wool', 'Nylon', 'Denim', 'Spandex', 'Rayon', 'Velvet', 'Fleece', 'Chiffon', 'Acrylic'
                ],
            ],
            'bags' => [
                'Material' => [
                    'Leather', 'Canvas', 'Nylon', 'Suede', 'Polyester', 'Jute', 'Vegan Leather', 'PVC', 'Wool', 'Satin', 'Cordura'
                ],
                'Size' => [
                    'Small', 'Medium', 'Large', 'Extra Large', 'Mini', 'One Size'
                ],
                'Color' => [
                    'Red', 'Blue', 'Black', 'Brown', 'White', 'Pink', 'Purple', 'Beige', 'Green', 'Yellow', 'Orange', 'Gray', 'Navy', 'Tan'
                ],
                'Style' => [
                    'Tote', 'Backpack', 'Crossbody', 'Clutch', 'Satchel', 'Messenger', 'Duffel', 'Shoulder', 'Hobo', 'Briefcase'
                ]
            ],

            'furniture' => [
                'Material' => [
                    'Wood', 'Metal', 'Plastic', 'Glass', 'Leather', 'Fabric', 'Marble', 'Stone', 'Concrete', 'Rattan', 'Bamboo', 'Mirrored', 'Polyurethane', 'Velvet'
                ],
                'Dimensions' => [
                    '100x50x30', '150x75x50', '200x100x75', '120x60x40', '180x90x60', '250x150x100', '90x45x30', '60x30x20'
                ],
                'Weight Capacity' => [
                    '50kg', '100kg', '200kg', '300kg', '500kg', '1000kg'
                ],
                'Color' => [
                    'Red', 'Blue', 'Green', 'Black', 'White', 'Gray', 'Brown', 'Beige', 'Tan', 'Navy', 'Olive', 'Gold', 'Silver', 'Cream', 'Wooden Finish'
                ],
            ],

            'books' => [
                'Author' => ['Author A', 'Author B', 'Author C'],
                'Genre' => ['Fiction', 'Non-fiction', 'Sci-fi'],
                'Language' => ['English', 'Spanish', 'French']
            ],
            'gadgets' => [
                'Model' => ['Model X', 'Model Y', 'Model Z', 'ProMax', 'UltraX', 'ElitePlus', 'SmartOne', 'Vision'],
                'Specifications' => [
                    'Spec 1', 'Spec 2', 'Spec 3', '4GB RAM', '8GB RAM', '16GB RAM', '64GB Storage', '128GB Storage', '256GB Storage', 'Full HD Display', '4K Display', 'Bluetooth 5.0', 'Wi-Fi 6', '5000mAh Battery', 'Fast Charging', 'Water Resistant', 'GPS Enabled', 'NFC Support', 'Wireless Charging', 'Fingerprint Scanner', 'Face Recognition'
                ],
                'Color' => [
                    'Black', 'White', 'Silver', 'Gold', 'Blue', 'Red', 'Green', 'Pink', 'Purple', 'Gray', 'Rose Gold', 'Copper'
                ],
                'Size' => [
                    'Small', 'Medium', 'Large', 'Compact', 'Slim', 'Standard'
                ],
            ],
            'animals-pet' => [
                'Age' => [
                    'Puppy', 'Kitten', 'Adult', 'Senior', 'Newborn'
                ],
                'Size' => [
                    'Small', 'Medium', 'Large', 'Extra Large', 'Tiny', 'Miniature'
                ],
                'Weight' => [
                    'Under 5kg', '5kg - 10kg', '10kg - 20kg', '20kg - 40kg', '40kg and above'
                ],
                'Color' => [
                    'Black', 'White', 'Brown', 'Golden', 'Gray', 'Spotted', 'Tan', 'Multi-Color', 'Cream', 'Red', 'Blue', 'Striped', 'Spotted'
                ],
            ],
            'fish' => [
                'weight' => [
                    'Under 1kg', '1kg - 2kg', '2kg - 5kg', '5kg - 10kg', 'Above 10kg'
                ],
                'Fish Size' => [
                    'Small', 'Medium', 'Large', 'Extra Large', 'Miniature', 'Jumbo'
                ],
                'Fish Location' => [
                    'Atlantic', 'Pacific', 'Indian Ocean', 'Arctic Ocean', 'Mediterranean Sea', 'Caribbean Sea', 'South China Sea', 'Gulf of Mexico', 'Great Barrier Reef'
                ],
                'Fish Color' => [
                    'Silver', 'Red', 'Pink', 'Brown', 'Golden', 'Green', 'Blue', 'White', 'Black', 'Spotted'
                ],
                'Packaging' => [
                    'Whole Fish', 'Fillet', 'Steak', 'Sliced', 'Minced', 'Canned', 'Frozen', 'Smoked'
                ],
                'Storage Method' => [
                    'Refrigerate', 'Freeze', 'Cool Storage'
                ]
            ],
        ];


        // grocery
        $products = [];
        $product_names = [
            "Fruits" => ["Fresh Apples", "Organic Bananas", "Mangoes", "Strawberries", "Blueberries", "Pineapple"],
            "Dairy" => ["Cheddar Cheese", "Greek Yogurt", "Whole Milk", "Butter", "Cream Cheese", "Cottage Cheese"],
            "Beverages" => ["Grape Juice", "Lemonade", "Green Tea"],
            "Snacks" => ["Granola Bars", "Whole Grain Crackers", "Tortilla Chips"],
            "Meat & Seafood" => ["Frozen Chicken Breasts", "Frozen Shrimp"],
            "Canned" => ["Canned Tuna", "Canned Corn", "Canned Tomatoes"],
            "Spices" => ["Brown Sugar", "Balsamic Vinegar", "Honey"],
            "Personal Care" => ["Coconut Oil", "Almond Oil", "Shea Butter"],
            "Cleaning Supplies" => ["Dish Soap", "All-Purpose Cleaner", "Glass Cleaner"],
        ];
        $flattened_product_names = array_merge(...array_values($product_names)); // Flatten array

        // Create brands for GROCERY
        $brands = [
            'GROCERY' => ['Organic Valley', 'Earth’s Best', 'Whole Foods Market'],
        ];
        $brand_ids = [];
        foreach ($brands['GROCERY'] as $index => $brand_name) {
            $brand = ProductBrand::create([
                'brand_name' => $brand_name,
                'brand_slug' => strtolower(str_replace(' ', '-', $brand_name)),
                'brand_logo' => '1',
                'meta_title' => 'Meta Title for ' . $brand_name,
                'meta_description' => 'Meta description for ' . $brand_name,
                'seller_relation_with_brand' => 'Seller relation description for ' . $brand_name,
                'authorization_valid_from' => now(),
                'authorization_valid_to' => now()->addYear(),
                'display_order' => $index + 1,
                'created_by' => 1,
                'updated_by' => 1,
                'status' => 1,
            ]);
            $brand_ids[] = $brand->id;
        }
        $store_info = Branch::select('id')->where('store_type', ProductType::GROCERY->value)->first();

        for ($i = 0; $i < count($flattened_product_names); $i++) {
            $product_name = $flattened_product_names[$i % count($flattened_product_names)];
            $unique_suffix = $i + 1; // Ensuring uniqueness in slug
            $slug = strtolower(str_replace(' ', '-', $product_name)) . '-' . $unique_suffix;
            $slug = substr($slug, 0, 255);

            // Randomly select a brand_id from the brand_ids array
            $brand_id = $brand_ids[array_rand($brand_ids)];
            $products[] = Product::create([
                'store_id' => $store_info->id,
                'category_id' => null,
                'brand_id' => $brand_id,
                'unit_id' => 1,
                'type' => 'grocery',
                'behaviour' => Behaviour::CONSUMABLE->value, // valid behaviour
                'name' => $product_name, // Prevent overflow
                'slug' => $slug, // Unique slug
                'description' => "{$product_name} are fresh and of premium quality, perfect for your daily needs. Stock up and enjoy every bite!",
                'image' => "1",
                'warranty' => json_encode([
                    ['warranty_period' => rand(1, 5), 'warranty_text' => 'Years Warranty']
                ]),
                'class' => 'default',
                'return_in_days' => rand(7, 30),
                'return_text' => 'Return within the specified days.',
                'allow_change_in_mind' => 'Yes',
                'cash_on_delivery' => rand(0, 1) * 100,
                'delivery_time_min' => rand(1, 2),
                'delivery_time_max' => rand(3, 7),
                'delivery_time_text' => 'Can be delayed during holidays.',
                'max_cart_qty' => rand(1, 10),
                'order_count' => rand(0, 100),
                'views' => rand(0, 1000),
//                'status' => StatusType::cases()[array_rand(StatusType::cases())]->value,
                'status' => 'approved',
                'meta_title' => "Buy {$product_name} online",
                'meta_description' => "Order {$product_name} online and get fresh groceries delivered to your door.",
                'meta_keywords' => "grocery, {$product_name}, fresh, $i",
                'meta_image' => "grocery-product$i-meta.jpg",
                'available_time_starts' => now(),
                'available_time_ends' => now()->addDays(30),
            ]);
        }

        // bakery
        $products = [];
        $product_names = [
            'Bread' => ["Sourdough Bread", "French Baguette"],
            'Pastries' => ["Croissants", "Cinnamon Buns"],
            'Cakes' => ["Chocolate Muffins", "Blueberry Scones"],
            'Cookies' => ["Butter Rolls", "Multigrain Loaf"],
            'Muffins' => ["Chocolate Muffins", "Blueberry Scones"],
            'Buns' => ["Cinnamon Buns", "Butter Rolls"],
            'Pies' => ["Ciabatta Bread", "Rye Bread"],
            'Bagels' => ["Sourdough Bread", "French Baguette"],
        ];
        $flattened_product_names = array_merge(...array_values($product_names)); // Flatten array

        $brands = [
            'BAKERY' => ['King Arthur', 'Bimbo Bakeries', 'Sara Lee'],
        ];
        $brand_ids = [];

        foreach ($brands['BAKERY'] as $index => $brand_name) {
            $brand = ProductBrand::create([
                'brand_name' => $brand_name,
                'brand_slug' => strtolower(str_replace(' ', '-', $brand_name)),
                'brand_logo' => '1',
                'meta_title' => 'Meta Title for ' . $brand_name,
                'meta_description' => 'Meta description for ' . $brand_name,
                'seller_relation_with_brand' => 'Seller relation description for ' . $brand_name,
                'authorization_valid_from' => now(),
                'authorization_valid_to' => now()->addYear(),
                'display_order' => $index + 1,
                'created_by' => 1,
                'updated_by' => 1,
                'status' => 1,
            ]);
            // Ensure the brand ID is added
            if ($brand) {
                $brand_ids[] = $brand->id;
            }
        }

        $store_info = Branch::select('id')->where('store_type', ProductType::BAKERY->value)->first();

        for ($i = 0; $i < count($flattened_product_names); $i++) {
            $product_name = $flattened_product_names[$i % count($flattened_product_names)];
            $unique_suffix = $i + 1; // Ensuring uniqueness in slug
            $slug = strtolower(str_replace(' ', '-', $product_name)) . '-' . $unique_suffix;
            $slug = substr($slug, 0, 255);
            // Randomly select a brand_id from the brand_ids array
            if (!empty($brand_ids)) {
                $brand_id = $brand_ids[array_rand($brand_ids)];
            } else {
                // Handle case where no brand exists (optional)
                $brand_id = null;
            }

            $products[] = Product::create([
                'store_id' => $store_info->id,
                'category_id' => null,
                'brand_id' => $brand_id,
                'unit_id' => 1,
                'type' => 'bakery',
                'behaviour' => Behaviour::CONSUMABLE->value, // valid behaviour
                'name' => $product_name,
                'slug' => $slug,
                'description' => "{$product_name} is freshly baked and of premium quality, perfect for your daily needs. Stock up and enjoy every bite!",
                'image' => "1",
                'warranty' => json_encode([
                    ['warranty_period' => rand(1, 5), 'warranty_text' => 'Days Warranty']
                ]),
                'class' => 'default',
                'return_in_days' => rand(1, 7),
                'return_text' => 'Return within the specified days.',
                'allow_change_in_mind' => 'Yes',
                'cash_on_delivery' => rand(0, 1) * 100,
                'delivery_time_min' => rand(1, 2),
                'delivery_time_max' => rand(3, 7),
                'delivery_time_text' => 'Can be delayed during holidays.',
                'max_cart_qty' => rand(1, 10),
                'order_count' => rand(0, 100),
                'views' => rand(0, 1000),
//                'status' => StatusType::cases()[array_rand(StatusType::cases())]->value,
                'status' => 'approved',
                'meta_title' => "Buy {$product_name} online",
                'meta_description' => "Order {$product_name} online and get freshly baked goods delivered to your door.",
                'meta_keywords' => "bakery, {$product_name}, fresh, $i",
                'meta_image' => "bakery-product$i-meta.jpg",
                'available_time_starts' => now(),
                'available_time_ends' => now()->addDays(30),
            ]);
        }

        //medicine
        $products = [];
        $product_names = [
            'Pain Relief' => ["Paracetamol Tablets", "Ibuprofen Capsules"],
            'Cold & Cough' => ["Cough Syrup", "Cetirizine Antihistamine"],
            'Vitamins' => ["Vitamin C Tablets", "Multivitamin Capsules"],
            'Digestive' => ["Omeprazole 20mg", "Calcium Supplements"],
            'BP & Heart Disease' => ["Aspirin 500mg", "Amoxicillin Antibiotic"],
            'Skin Care' => ["Cetirizine Antihistamine", "Ibuprofen Capsules"],
            'Eye Care' => ["Vitamin C Tablets", "Calcium Supplements"],
            'Herbal' => ["Multivitamin Capsules", "Paracetamol Tablets"],
        ];
        $flattened_product_names = array_merge(...array_values($product_names)); // Flatten array

        $brands = [
            'MEDICINE' => ['Pfizer', 'Johnson & Johnson', 'Novartis'],
        ];
        $brand_ids = [];

        foreach ($brands['MEDICINE'] as $index => $brand_name) {
            $brand = ProductBrand::create([
                'brand_name' => $brand_name,
                'brand_slug' => strtolower(str_replace(' ', '-', $brand_name)),
                'brand_logo' => '1',
                'meta_title' => 'Meta Title for ' . $brand_name,
                'meta_description' => 'Meta description for ' . $brand_name,
                'seller_relation_with_brand' => 'Seller relation description for ' . $brand_name,
                'authorization_valid_from' => now(),
                'authorization_valid_to' => now()->addYear(),
                'display_order' => $index + 1,
                'created_by' => 1,
                'updated_by' => 1,
                'status' => 1,
            ]);
            $brand_ids[] = $brand->id;
        }

        $store_info = Branch::select('id')->where('store_type', ProductType::MEDICINE->value)->first();
        for ($i = 0; $i < count($flattened_product_names); $i++) {
            $product_name = $flattened_product_names[$i % count($flattened_product_names)];
            $unique_suffix = $i + 1; // Ensuring uniqueness in slug
            $slug = strtolower(str_replace(' ', '-', $product_name)) . '-' . $unique_suffix;
            $slug = substr($slug, 0, 255);
            // Randomly select a brand_id from the brand_ids array
            $brand_id = $brand_ids[array_rand($brand_ids)];

            $products[] = Product::create([
                'store_id' => $store_info->id,
                'category_id' => null,
                'brand_id' => $brand_id,
                'unit_id' => 1,
                'type' => 'medicine',
                'behaviour' => Behaviour::CONSUMABLE->value, // valid behaviour
                'name' => $product_name,
                'slug' => $slug,
                'description' => "{$product_name} is a high-quality pharmaceutical product designed for effective treatment and relief.",
                'image' => "1",
                'warranty' => json_encode([
                    ['warranty_period' => rand(1, 2), 'warranty_text' => 'Months Warranty']
                ]),
                'class' => 'default',
                'return_in_days' => rand(1, 14),  // Shorter return period for medicines
                'return_text' => 'Return within the specified days if the packaging is unopened.',
                'allow_change_in_mind' => 'No',  // Medicines typically cannot be returned after purchase
                'cash_on_delivery' => rand(0, 1) * 100,
                'delivery_time_min' => rand(1, 2),
                'delivery_time_max' => rand(3, 5),
                'delivery_time_text' => 'Delivery time may vary based on location and availability.',
                'max_cart_qty' => rand(1, 5),  // Limiting quantity for medicine purchases
                'order_count' => rand(0, 100),
                'views' => rand(0, 1000),
//                'status' => StatusType::cases()[array_rand(StatusType::cases())]->value,
                'status' => 'approved',
                'meta_title' => "Buy {$product_name} online",
                'meta_description' => "Order {$product_name} online and get quality medicines delivered safely to your home.",
                'meta_keywords' => "medicine, {$product_name}, healthcare, pharmacy, $i",
                'meta_image' => "medicine-product$i-meta.jpg",
                'available_time_starts' => now(),
                'available_time_ends' => now()->addDays(60),
            ]);
        }

        // makeup
        $products = [];
        $product_names = [
            'Foundations' => ["Liquid Foundation", "BB Cream"],
            'Lipsticks' => ["Matte Lipstick", "Translucent Powder"],
            'Eyeshadows' => ["Eyebrow Pomade", "Highlighter Stick"],
            'Mascaras' => ["Waterproof Mascara", "Setting Spray"],
            'Blushes' => ["Blush Palette", "Matte Lipstick"],
        ];
        $flattened_product_names = array_merge(...array_values($product_names)); // Flatten array

        // Create brands for GROCERY
        $brands = [
            'MAKEUP' => ['Maybelline', 'L’Oreal', 'MAC Cosmetics'],
        ];
        $brand_ids = [];
        foreach ($brands['MAKEUP'] as $index => $brand_name) {
            $brand = ProductBrand::create([
                'brand_name' => $brand_name,
                'brand_slug' => strtolower(str_replace(' ', '-', $brand_name)),
                'brand_logo' => '1',
                'meta_title' => 'Meta Title for ' . $brand_name,
                'meta_description' => 'Meta description for ' . $brand_name,
                'seller_relation_with_brand' => 'Seller relation description for ' . $brand_name,
                'authorization_valid_from' => now(),
                'authorization_valid_to' => now()->addYear(),
                'display_order' => $index + 1,
                'created_by' => 1,
                'updated_by' => 1,
                'status' => 1,
            ]);
            $brand_ids[] = $brand->id;
        }

        $store_info = Branch::select('id')->where('store_type', ProductType::MAKEUP->value)->first();
        for ($i = 0; $i < count($flattened_product_names); $i++) {
            $product_name = $flattened_product_names[$i % count($flattened_product_names)];
            $unique_suffix = $i + 1; // Ensuring uniqueness in slug
            $slug = strtolower(str_replace(' ', '-', $product_name)) . '-' . $unique_suffix;
            $slug = substr($slug, 0, 255);

            // Randomly select a brand_id from the brand_ids array
            $brand_id = $brand_ids[array_rand($brand_ids)];

            $products[] = Product::create([
                'store_id' => $store_info->id,
                'category_id' => null,
                'brand_id' => $brand_id,
                'unit_id' => 1,
                'type' => 'makeup',
                'behaviour' => Behaviour::PHYSICAL->value, // valid behaviour
                'name' => $product_name,
                'slug' => $slug,
                'description' => "{$product_name} enhances your beauty with a flawless finish, designed for long-lasting wear.",
                'image' => "1",
                'warranty' => json_encode([
                    ['warranty_period' => rand(1, 2), 'warranty_text' => 'Months Warranty']
                ]),
                'class' => 'default',
                'return_in_days' => rand(7, 30),
                'return_text' => 'Return within the specified days if unopened and unused.',
                'allow_change_in_mind' => 'Yes',
                'cash_on_delivery' => rand(0, 1) * 100,
                'delivery_time_min' => rand(1, 2),
                'delivery_time_max' => rand(3, 7),
                'delivery_time_text' => 'Can be delayed during holidays.',
                'max_cart_qty' => rand(1, 5),  // Limited purchase for makeup items
                'order_count' => rand(0, 100),
                'views' => rand(0, 1000),
//                'status' => StatusType::cases()[array_rand(StatusType::cases())]->value,
                'status' => 'approved',
                'meta_title' => "Buy {$product_name} online",
                'meta_description' => "Order {$product_name} online and get premium beauty products delivered to your doorstep.",
                'meta_keywords' => "makeup, {$product_name}, beauty, cosmetics, $i",
                'meta_image' => "makeup-product$i-meta.jpg",
                'available_time_starts' => now(),
                'available_time_ends' => now()->addDays(60),
            ]);
        }

        // bags
        $products = [];
        $product_names = [
            'Handbags' => ["Luxury Handbag", "Leather Messenger Bag"],
            'Totes' => ["Canvas Tote Bag", "Travel Duffel Bag"],
            'Backpacks' => ["Vintage Backpack", "Laptop Backpack"],
            'Wallets' => ["Mini Shoulder Bag", "Casual Sling Bag"],
            'Clutches' => ["Leather Messenger Bag", "Crossbody Purse"],
            'Crossbody' => ["Crossbody Purse", "Casual Sling Bag"],
        ];
        $flattened_product_names = array_merge(...array_values($product_names)); // Flatten array

        // Create brands for BAGS
        $brands = [
            'BAGS' => ['Michael Kors', 'Coach', 'Kate Spade'],
        ];
        $brand_ids = [];
        foreach ($brands['BAGS'] as $index => $brand_name) {
            $brand = ProductBrand::create([
                'brand_name' => $brand_name,
                'brand_slug' => strtolower(str_replace(' ', '-', $brand_name)),
                'brand_logo' => '1',
                'meta_title' => 'Meta Title for ' . $brand_name,
                'meta_description' => 'Meta description for ' . $brand_name,
                'seller_relation_with_brand' => 'Seller relation description for ' . $brand_name,
                'authorization_valid_from' => now(),
                'authorization_valid_to' => now()->addYear(),
                'display_order' => $index + 1,
                'created_by' => 1,
                'updated_by' => 1,
                'status' => 1,
            ]);
            $brand_ids[] = $brand->id;
        }


        $store_info = Branch::select('id')->where('store_type', ProductType::BAGS->value)->first();
        for ($i = 0; $i < count($flattened_product_names); $i++) {
            $product_name = $flattened_product_names[$i % count($flattened_product_names)];
            $unique_suffix = $i + 1; // Ensuring uniqueness in slug
            $slug = strtolower(str_replace(' ', '-', $product_name)) . '-' . $unique_suffix;
            $slug = substr($slug, 0, 255);

            // Randomly select a brand_id from the brand_ids array
            $brand_id = $brand_ids[array_rand($brand_ids)];

            $products[] = Product::create([
                'store_id' => $store_info->id,
                'category_id' => null,
                'brand_id' => $brand_id,
                'unit_id' => 1,
                'type' => 'bags',
                'behaviour' => Behaviour::PHYSICAL->value, // valid behaviour
                'name' => $product_name,
                'slug' => $slug,
                'description' => "{$product_name} is stylish, durable, and perfect for your everyday needs.",
                'image' => "1",
                'warranty' => json_encode([
                    ['warranty_period' => rand(1, 5), 'warranty_text' => 'Years Warranty']
                ]),
                'class' => 'default',
                'return_in_days' => rand(7, 30),
                'return_text' => 'Return within the specified days if unused.',
                'allow_change_in_mind' => 'Yes',
                'cash_on_delivery' => rand(0, 1) * 100,
                'delivery_time_min' => rand(1, 2),
                'delivery_time_max' => rand(3, 7),
                'delivery_time_text' => 'Can be delayed during holidays.',
                'max_cart_qty' => rand(1, 5),  // Bags are usually purchased in limited quantities
                'order_count' => rand(0, 100),
                'views' => rand(0, 1000),
//                'status' => StatusType::cases()[array_rand(StatusType::cases())]->value,
                'status' => 'approved',
                'meta_title' => "Buy {$product_name} online",
                'meta_description' => "Order {$product_name} online and get high-quality bags delivered to your doorstep.",
                'meta_keywords' => "bags, {$product_name}, travel, fashion, accessories, $i",
                'meta_image' => "bag-product$i-meta.jpg",
                'available_time_starts' => now(),
                'available_time_ends' => now()->addDays(60),
            ]);
        }


        // clothing
        $products = [];
        $product_names = [
            'Men' => ["Classic White T-Shirt", "Slim Fit Jeans", "Cotton Polo Shirt", "Hooded Sweatshirt", "Denim Jacket"],
            'Women' => ["Casual Chino Pants", "Athletic Joggers", "Formal Dress Shirt", "Wool Blend Coat", "Basic Crew Neck Sweater"],
        ];
        $flattened_product_names = array_merge(...array_values($product_names)); // Flatten array

        // Create brands for
        $brands = [
            'CLOTHING' => ['Levi’s', 'Nike', 'H&M'],
        ];
        $brand_ids = [];
        foreach ($brands['CLOTHING'] as $index => $brand_name) {
            $brand = ProductBrand::create([
                'brand_name' => $brand_name,
                'brand_slug' => strtolower(str_replace(' ', '-', $brand_name)),
                'brand_logo' => '1',
                'meta_title' => 'Meta Title for ' . $brand_name,
                'meta_description' => 'Meta description for ' . $brand_name,
                'seller_relation_with_brand' => 'Seller relation description for ' . $brand_name,
                'authorization_valid_from' => now(),
                'authorization_valid_to' => now()->addYear(),
                'display_order' => $index + 1,
                'created_by' => 1,
                'updated_by' => 1,
                'status' => 1,
            ]);
            $brand_ids[] = $brand->id;
        }

        $store_info = Branch::select('id')->where('store_type', ProductType::CLOTHING->value)->first();
        for ($i = 0; $i < count($flattened_product_names); $i++) {
            $product_name = $flattened_product_names[$i % count($flattened_product_names)];
            $unique_suffix = $i + 1; // Ensuring uniqueness in slug
            $slug = strtolower(str_replace(' ', '-', $product_name)) . '-' . $unique_suffix;
            $slug = substr($slug, 0, 255);

            // Randomly select a brand_id from the brand_ids array
            $brand_id = $brand_ids[array_rand($brand_ids)];

            $products[] = Product::create([
                'store_id' => $store_info->id,
                'category_id' => null,
                'brand_id' => $brand_id,
                'unit_id' => 1,
                'type' => 'clothing',
                'behaviour' => Behaviour::PHYSICAL->value, // valid behaviour
                'name' => $product_name,
                'slug' => $slug,
                'description' => "{$product_name} is stylish, comfortable, and perfect for your wardrobe.",
                'image' => "1",
                'warranty' => json_encode([
                    ['warranty_period' => rand(1, 2), 'warranty_text' => 'Months Warranty'] // Clothing usually has a shorter warranty
                ]),
                'class' => 'default',
                'return_in_days' => rand(7, 30),
                'return_text' => 'Return within the specified days if unworn.',
                'allow_change_in_mind' => 'Yes',
                'cash_on_delivery' => rand(0, 1) * 100,
                'delivery_time_min' => rand(1, 2),
                'delivery_time_max' => rand(3, 7),
                'delivery_time_text' => 'Delivery may take longer during peak seasons.',
                'max_cart_qty' => rand(1, 5),
                'order_count' => rand(0, 500),
                'views' => rand(0, 5000),
//                'status' => StatusType::cases()[array_rand(StatusType::cases())]->value,
                'status' => 'approved',
                'meta_title' => "Buy {$product_name} online",
                'meta_description' => "Order {$product_name} online and update your wardrobe with the latest fashion trends.",
                'meta_keywords' => "clothing, fashion, {$product_name}, apparel, $i",
                'meta_image' => "clothing-product$i-meta.jpg",
                'available_time_starts' => now(),
                'available_time_ends' => now()->addDays(60),
            ]);
        }

        // furniture
        $products = [];
        $product_names = [
            'Sofas' => ["Luxury Leather Sofa", "Victorian Elegance Sofa"],
            'Chairs' => ["Adjustable Office Chair", "Wingback Chair"],
            'Beds' => ["Queen Bed Frame", "Adjustable Bed"],
            'Tables' => ["Modern Wooden Dining Table", "Minimalist Coffee Table"],
            'Dressers' => ["Classic Oak Wardrobe", "Vintage Style Dresser"],
            'Bookshelves' => ["Minimalist Open Shelf", "Floating Zigzag Shelf"],
            'Desks' => ["Glass Top Work Desk", "Minimalist Wooden Desk"],
        ];

        $flattened_product_names = array_merge(...array_values($product_names)); // Flatten array

        // Create brands for FURNITURE
        $brands = [
            'FURNITURE' => ['Ikea', 'Ashley Furniture', 'Wayfair'],
        ];
        $brand_ids = [];
        foreach ($brands['FURNITURE'] as $index => $brand_name) {
            $brand = ProductBrand::create([
                'brand_name' => $brand_name,
                'brand_slug' => strtolower(str_replace(' ', '-', $brand_name)),
                'brand_logo' => '1',
                'meta_title' => 'Meta Title for ' . $brand_name,
                'meta_description' => 'Meta description for ' . $brand_name,
                'seller_relation_with_brand' => 'Seller relation description for ' . $brand_name,
                'authorization_valid_from' => now(),
                'authorization_valid_to' => now()->addYear(),
                'display_order' => $index + 1,
                'created_by' => 1,
                'updated_by' => 1,
                'status' => 1,
            ]);
            $brand_ids[] = $brand->id;
        }

        $store_info = Branch::select('id')->where('store_type', ProductType::FURNITURE->value)->first();
        for ($i = 0; $i < count($flattened_product_names); $i++) {
            $product_name = $flattened_product_names[$i % count($flattened_product_names)];
            $unique_suffix = $i + 1; // Ensuring uniqueness in slug
            $slug = strtolower(str_replace(' ', '-', $product_name)) . '-' . $unique_suffix;
            $slug = substr($slug, 0, 255);

            // Randomly select a brand_id from the brand_ids array
            $brand_id = $brand_ids[array_rand($brand_ids)];

            $products[] = Product::create([
                'store_id' => $store_info->id,
                'category_id' => null,
                'brand_id' => $brand_id,
                'unit_id' => 1,
                'type' => 'furniture',
                'behaviour' => Behaviour::PHYSICAL->value, // valid behaviour
                'name' => $product_name,
                'slug' => $slug,
                'description' => "{$product_name} is crafted with high-quality materials, offering durability and style for your space.",
                'image' => "1",
                'warranty' => json_encode([
                    ['warranty_period' => rand(1, 10), 'warranty_text' => 'Years Warranty'] // Longer warranty for furniture
                ]),
                'class' => 'default',
                'return_in_days' => rand(7, 30),
                'return_text' => 'Return within the specified days if unused and in original packaging.',
                'allow_change_in_mind' => 'Yes',
                'cash_on_delivery' => rand(0, 1) * 100,
                'delivery_time_min' => rand(3, 5),
                'delivery_time_max' => rand(7, 14),
                'delivery_time_text' => 'Delivery may take longer due to size and handling.',
                'max_cart_qty' => rand(1, 3),
                'order_count' => rand(0, 500),
                'views' => rand(0, 5000),
//                'status' => StatusType::cases()[array_rand(StatusType::cases())]->value,
                'status' => 'approved',
                'meta_title' => "Buy {$product_name} online",
                'meta_description' => "Order {$product_name} online and upgrade your home with premium furniture.",
                'meta_keywords' => "furniture, home decor, {$product_name}, interior design, $i",
                'meta_image' => "furniture-product$i-meta.jpg",
                'available_time_starts' => now(),
                'available_time_ends' => now()->addDays(90),
            ]);
        }

        // books
        $products = [];
        $product_names = [
            'Fiction' => ["To Kill a Mockingbird", "Pride and Prejudice"],
            'Non-Fiction' => ["The Art of War", "War and Peace"],
            'Sci-Fi' => ["The Hobbit", "The Great Gatsby"],
            'Fantasy' => ["The Divine Comedy", "Moby Dick"],
            'Biography' => ["Crime and Punishment", "The Catcher in the Rye"],
        ];
        $flattened_product_names = array_merge(...array_values($product_names)); // Flatten array

        // Create brands for BOOKS
        $brands = [
            'BOOKS' => ['Penguin Books', 'HarperCollins', 'Random House'],
        ];
        $brand_ids = [];
        foreach ($brands['BOOKS'] as $index => $brand_name) {
            $brand = ProductBrand::create([
                'brand_name' => $brand_name,
                'brand_slug' => strtolower(str_replace(' ', '-', $brand_name)),
                'brand_logo' => '1',
                'meta_title' => 'Meta Title for ' . $brand_name,
                'meta_description' => 'Meta description for ' . $brand_name,
                'seller_relation_with_brand' => 'Seller relation description for ' . $brand_name,
                'authorization_valid_from' => now(),
                'authorization_valid_to' => now()->addYear(),
                'display_order' => $index + 1,
                'created_by' => 1,
                'updated_by' => 1,
                'status' => 1,
            ]);
            $brand_ids[] = $brand->id;
        }

        $store_info = Branch::select('id')->where('store_type', ProductType::BOOKS->value)->first();
        for ($i = 0; $i < count($flattened_product_names); $i++) {
            $product_name = $flattened_product_names[$i % count($flattened_product_names)];
            $unique_suffix = $i + 1; // Ensuring uniqueness in slug
            $slug = strtolower(str_replace(' ', '-', $product_name)) . '-' . $unique_suffix;
            $slug = substr($slug, 0, 255);

            // Randomly select a brand_id from the brand_ids array
            $brand_id = $brand_ids[array_rand($brand_ids)];

            $products[] = Product::create([
                'store_id' => $store_info->id,
                'category_id' => null,
                'brand_id' => $brand_id,
                'unit_id' => 1,
                'type' => 'books',
                'behaviour' => Behaviour::PHYSICAL->value, // valid behaviour
                'name' => $product_name,
                'slug' => $slug,
                'description' => "{$product_name} is a timeless classic, offering valuable insights and stories from the past.",
                'image' => "1",
                'warranty' => json_encode([
                    ['warranty_period' => rand(1, 3), 'warranty_text' => 'Years Warranty'] // Books typically have a shorter warranty
                ]),
                'class' => 'default',
                'return_in_days' => rand(7, 30),
                'return_text' => 'Return within the specified days if unused and in original condition.',
                'allow_change_in_mind' => 'Yes',
                'cash_on_delivery' => rand(0, 1) * 100,
                'delivery_time_min' => rand(1, 2),
                'delivery_time_max' => rand(3, 7),
                'delivery_time_text' => 'Delivery may be delayed due to high demand during holidays.',
                'max_cart_qty' => rand(1, 10),
                'order_count' => rand(0, 1000),
                'views' => rand(0, 5000),
//                'status' => StatusType::cases()[array_rand(StatusType::cases())]->value,
                'status' => 'approved',
                'meta_title' => "Buy {$product_name} online",
                'meta_description' => "Order {$product_name} online and enjoy the world of literature delivered to your doorstep.",
                'meta_keywords' => "book, {$product_name}, classic, literature, $i",
                'meta_image' => "book-product$i-meta.jpg",
                'available_time_starts' => now(),
                'available_time_ends' => now()->addDays(30),
            ]);
        }

        // gadgets
        $products = [];
        $product_names = [
            'Phones' => ["iPhone 16 Pro Max", "Samsung Galaxy S25", "Wireless Charger"],
            'Tablets' => ["iPad Air M2", "Galaxy Tab S9"],
            'Headphones' => ["Wireless Earbuds", "Noise Cancelling Headphones"],
            'Smart Watches' => ["Smartwatch Series 5", "Bluetooth Speaker"],
            'Laptops' => ["Laptop Sleeve", "Gaming Mouse"],
            'Cameras' => ["Action Camera", "4K LED TV"],
        ];
        $flattened_product_names = array_merge(...array_values($product_names)); // Flatten array

        $brands = ['GADGET' => ['Apple', 'Samsung', 'Sony']];
        $brand_ids = [];
        foreach ($brands['GADGET'] as $index => $brand_name) {
            $brand = ProductBrand::create([
                'brand_name' => $brand_name,
                'brand_slug' => strtolower(str_replace(' ', '-', $brand_name)),
                'brand_logo' => '1',
                'meta_title' => 'Meta Title for ' . $brand_name,
                'meta_description' => 'Meta description for ' . $brand_name,
                'seller_relation_with_brand' => 'Seller relation description for ' . $brand_name,
                'authorization_valid_from' => now(),
                'authorization_valid_to' => now()->addYear(),
                'display_order' => $index + 1,
                'created_by' => 1,
                'updated_by' => 1,
                'status' => 1,
            ]);
            $brand_ids[] = $brand->id;
        }

        $store_info = Branch::select('id')->where('store_type', ProductType::GADGET->value)->first();
        for ($i = 0; $i < count($flattened_product_names); $i++) {
            $product_name = $flattened_product_names[$i % count($flattened_product_names)];
            $unique_suffix = $i + 1; // Ensuring uniqueness in slug
            $slug = strtolower(str_replace(' ', '-', $product_name)) . '-' . $unique_suffix;
            $slug = substr($slug, 0, 255);

            // Randomly select a brand_id from the brand_ids array
            $brand_id = $brand_ids[array_rand($brand_ids)];

            $products[] = Product::create([
                'store_id' => $store_info->id,
                'category_id' => null,
                'brand_id' => $brand_id,
                'unit_id' => 1,
                'type' => 'gadgets',
                'behaviour' => Behaviour::PHYSICAL->value, // valid behaviour
                'name' => $product_name,
                'slug' => $slug,
                'description' => "{$product_name} offer the latest technology and superior performance for your daily needs.",
                'image' => "1",
                'warranty' => json_encode([
                    ['warranty_period' => rand(1, 3), 'warranty_text' => 'Years Warranty']
                ]),
                'class' => 'default',
                'return_in_days' => rand(7, 30),
                'return_text' => 'Return within the specified days if unused and in original condition.',
                'allow_change_in_mind' => 'Yes',
                'cash_on_delivery' => rand(0, 1) * 100,
                'delivery_time_min' => rand(1, 2),
                'delivery_time_max' => rand(3, 7),
                'delivery_time_text' => 'Delivery may be delayed due to high demand during holidays.',
                'max_cart_qty' => rand(1, 10),
                'order_count' => rand(0, 1000),
                'views' => rand(0, 5000),
//                'status' => StatusType::cases()[array_rand(StatusType::cases())]->value,
                'status' => 'approved',
                'meta_title' => "Buy {$product_name} online",
                'meta_description' => "Order {$product_name} online and enjoy the latest tech products delivered to your doorstep.",
                'meta_keywords' => "gadgets, {$product_name}, tech, $i",
                'meta_image' => "gadget-product$i-meta.jpg",
                'available_time_starts' => now(),
                'available_time_ends' => now()->addDays(30),
            ]);
        }

        // animals-pet
        $products = [];
        $product_names = [
            'Dogs' => ["Golden Retriever", "Bulldog"],
            'Cats' => ["Persian Cat", "Siamese Cat"],
            'Pet Toys' => ["Dog Chew Toy", "Interactive Cat Toy"],
            'Grooming' => ["Dog Shampoo", "Cat Brush"],
            'Pet Food' => ["Dog Food", "Cat Food"],
        ];
        $flattened_product_names = array_merge(...array_values($product_names)); // Flatten array

        $brands = [
            'ANIMALS_PET' => ['Pedigree', 'Hill’s Science Diet', 'Royal Canin'],
        ];
        $brand_ids = [];

        foreach ($brands['ANIMALS_PET'] as $index => $brand_name) {
            $brand = ProductBrand::create([
                'brand_name' => $brand_name,
                'brand_slug' => strtolower(str_replace(' ', '-', $brand_name)),
                'brand_logo' => '1',
                'meta_title' => 'Meta Title for ' . $brand_name,
                'meta_description' => 'Meta description for ' . $brand_name,
                'seller_relation_with_brand' => 'Seller relation description for ' . $brand_name,
                'authorization_valid_from' => now(),
                'authorization_valid_to' => now()->addYear(),
                'display_order' => $index + 1,
                'created_by' => 1,
                'updated_by' => 1,
                'status' => 1,
            ]);
            $brand_ids[] = $brand->id;
        }

        $store_info = Branch::select('id')->where('store_type', ProductType::ANIMALS_PET->value)->first();
        for ($i = 0; $i < count($flattened_product_names); $i++) {
            $product_name = $flattened_product_names[$i % count($flattened_product_names)];
            $unique_suffix = $i + 1; // Ensuring uniqueness in slug
            $slug = strtolower(str_replace(' ', '-', $product_name)) . '-' . $unique_suffix;
            $slug = substr($slug, 0, 255);

            // Randomly select a brand_id from the brand_ids array
            $brand_id = $brand_ids[array_rand($brand_ids)];

            $products[] = Product::create([
                'store_id' => $store_info->id,
                'category_id' => null,
                'brand_id' => $brand_id,
                'unit_id' => 1,
                'type' => 'animals-pet',
                'behaviour' => Behaviour::PHYSICAL->value, // valid behaviour
                'name' => $product_name,
                'slug' => $slug,
                'description' => "{$product_name} are of the highest quality, perfect for your pet's comfort and care.",
                'image' => "1",
                'warranty' => json_encode([
                    ['warranty_period' => rand(1, 3), 'warranty_text' => 'Years Warranty']
                ]),
                'class' => 'default',
                'return_in_days' => rand(7, 30),
                'return_text' => 'Return within the specified days if unused and in original condition.',
                'allow_change_in_mind' => 'Yes',
                'cash_on_delivery' => rand(0, 1) * 100,
                'delivery_time_min' => rand(1, 2),
                'delivery_time_max' => rand(3, 7),
                'delivery_time_text' => 'Can be delayed during holidays.',
                'max_cart_qty' => rand(1, 10),
                'order_count' => rand(0, 100),
                'views' => rand(0, 1000),
//                'status' => StatusType::cases()[array_rand(StatusType::cases())]->value,
                'status' => 'approved',
                'meta_title' => "Buy {$product_name} online",
                'meta_description' => "Order {$product_name} online and get the best products for your pets delivered to your door.",
                'meta_keywords' => "pet supplies, {$product_name}, pets, $i",
                'meta_image' => "pet-product$i-meta.jpg",
                'available_time_starts' => now(),
                'available_time_ends' => now()->addDays(30),
            ]);
        }

        // fish
        $products = [];
        $product_names = [
            'Freshwater' => ["Fresh Salmon Fillets", "Frozen Trout", "Frozen Shrimp"],
            'Saltwater' => ["Premium Tuna", "Mahi Mahi Fish"],
            'Aquarium Plants' => ["Caviar", "Fish Oil Supplement"],
            'Fish Food' => ["Canned Sardines", "Dried Anchovies"],
            'Water Care' => ["Smoked Salmon", "Frozen Shrimp"],
        ];
        $flattened_product_names = array_merge(...array_values($product_names)); // Flatten array

        $brands = [
            'FISH' => ['SeaPak', 'Wild Planet', 'Gorton’s'],
        ];
        $brand_ids = [];

        foreach ($brands['FISH'] as $index => $brand_name) {
            $brand = ProductBrand::create([
                'brand_name' => $brand_name,
                'brand_slug' => strtolower(str_replace(' ', '-', $brand_name)),
                'brand_logo' => '1',
                'meta_title' => 'Meta Title for ' . $brand_name,
                'meta_description' => 'Meta description for ' . $brand_name,
                'seller_relation_with_brand' => 'Seller relation description for ' . $brand_name,
                'authorization_valid_from' => now(),
                'authorization_valid_to' => now()->addYear(),
                'display_order' => $index + 1,
                'created_by' => 1,
                'updated_by' => 1,
                'status' => 1,
            ]);
            $brand_ids[] = $brand->id;
        }


        $store_info = Branch::select('id')->where('store_type', ProductType::FISH->value)->first();
        for ($i = 0; $i < count($flattened_product_names); $i++) {
            $product_name = $flattened_product_names[$i % count($flattened_product_names)];
            $unique_suffix = $i + 1; // Ensuring uniqueness in slug
            $slug = strtolower(str_replace(' ', '-', $product_name)) . '-' . $unique_suffix;
            $slug = substr($slug, 0, 255);

            // Randomly select a brand_id from the brand_ids array
            $brand_id = $brand_ids[array_rand($brand_ids)];

            $products[] = Product::create([
                'store_id' => $store_info->id,
                'category_id' => null,
                'brand_id' => $brand_id,
                'unit_id' => 1,
                'type' => 'fish',
                'behaviour' => Behaviour::PHYSICAL->value, // Random valid behaviour
                'name' => $product_name,
                'slug' => $slug,
                'description' => "{$product_name} are fresh and of premium quality, perfect for your daily seafood cravings.",
                'image' => "1",
                'warranty' => json_encode([
                    ['warranty_period' => rand(1, 3), 'warranty_text' => 'Years Warranty']
                ]),
                'class' => 'default',
                'return_in_days' => rand(7, 30),
                'return_text' => 'Return within the specified days if unused and in original condition.',
                'allow_change_in_mind' => 'Yes',
                'cash_on_delivery' => rand(0, 1) * 100,
                'delivery_time_min' => rand(1, 2),
                'delivery_time_max' => rand(3, 7),
                'delivery_time_text' => 'Can be delayed during holidays.',
                'max_cart_qty' => rand(1, 10),
                'order_count' => rand(0, 100),
                'views' => rand(0, 1000),
//                'status' => StatusType::cases()[array_rand(StatusType::cases())]->value,
                'status' => 'approved',
                'meta_title' => "Buy {$product_name} online",
                'meta_description' => "Order {$product_name} online and get the freshest fish and seafood delivered to your door.",
                'meta_keywords' => "fish, seafood, {$product_name}, fresh, $i",
                'meta_image' => "fish-product$i-meta.jpg",
                'available_time_starts' => now(),
                'available_time_ends' => now()->addDays(30),
            ]);
        }


        $products = Product::all();
        $units = Unit::all();
        foreach ($products as $product) {
            $this->createProductVariants($product, $units, $attributes_sets, 3);
        }
    }

    private function createProductVariants(Product $product, $units, $attributes_sets, $numberOfVariants = 3): void
    {
        for ($j = 1; $j <= $numberOfVariants; $j++) {
            $unit = $units->random();

            // Get the product type
            $product_type = $product->type;

            // Get the appropriate attributes set based on product type
            $attributes = $attributes_sets[$product_type] ?? [];

            // Randomly select attributes
            $random_attributes = [];
            foreach ($attributes as $attribute => $options) {
                $random_attributes[$attribute] = $options[array_rand($options)];
            }

            ProductVariant::create([
                'product_id' => $product->id,
                'variant_slug' => "product-{$product->id}-variant-{$j}",
                'sku' => "SKU-{$product->id}-{$j}",
                'pack_quantity' => rand(1, 10),
                'weight_major' => rand(100, 500),
                'weight_gross' => rand(500, 1000),
                'weight_net' => rand(400, 900),
                'attributes' => json_encode($random_attributes), // Store as JSON
                'price' => rand(100, 1000),
                'special_price' => rand(50, 500),
                'stock_quantity' => rand(10, 100),
                'unit_id' => $unit->id,
                'length' => rand(10, 50),
                'width' => rand(10, 50),
                'height' => rand(10, 50),
                'image' => '2',
                'order_count' => rand(0, 100),
                'status' => 1, // Random active/inactive
            ]);
        }
    }


    /**
     * Helper function to get random color.
     */
    private function getRandomColor()
    {
        $colors = ['red', 'blue', 'green', 'yellow', 'black', 'white'];
        return $colors[array_rand($colors)];
    }

    /**
     * Helper function to get random size.
     */
    private function getRandomSize()
    {
        $sizes = ['S', 'M', 'L', 'XL', 'XXL'];
        return $sizes[array_rand($sizes)];
    }

}
