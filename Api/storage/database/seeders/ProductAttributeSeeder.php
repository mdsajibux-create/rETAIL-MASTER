<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Modules\Catalog\app\Models\ProductAttribute;
use Modules\Catalog\app\Models\ProductAttributeValue;

class ProductAttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
                'Expiry Date' => [ '2025-12-31', '2026-06-30', '2027-01-01', '2027-12-31', '2028-06-30', '2028-12-31', '2029-01-15', '2029-06-30', '2029-12-31', '2030-06-30','2030-12-31',],
            ],
            'medicine' => [
                'Dosage' => ['50mg', '100mg', '200mg'],
                'Manufacturer' => ['Company A', 'Company B'],
                'Type' => ['Tablet', 'Capsule', 'Syrup', 'Injection'],
                'Expiry Date' => [ '2025-12-31', '2026-06-30', '2027-01-01', '2027-12-31', '2028-06-30', '2028-12-31', '2029-01-15', '2029-06-30', '2029-12-31', '2030-06-30','2030-12-31',],
            ],
            'makeup' => [
                'Shade' => ['Light', 'Medium', 'Dark', 'Fair', 'Tan', 'Deep'],
                'Volume' => ['15ml', '30ml', '50ml', '100ml'],
                'Skin Type' => ['Oily', 'Dry', 'Combination', 'Sensitive', 'Normal'],
                'Product Type' => ['Foundation', 'Concealer', 'Lipstick', 'Mascara', 'Eyeliner', 'Blush', 'Highlighter'],
                'Packaging' => ['Tube', 'Bottle', 'Compact', 'Palette'],
                'Expiry Date' => [ '2025-12-31', '2026-06-30', '2027-01-01', '2027-12-31', '2028-06-30', '2028-12-31', '2029-01-15', '2029-06-30', '2029-12-31', '2030-06-30','2030-12-31',],
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


        $currentTimestamp = Carbon::now();
        $data = [];

        foreach ($attributes_sets as $storeTypeKey => $storeTypeAttributes) {
            foreach ($storeTypeAttributes as $attributeName => $values) {
                $productAttribute = ProductAttribute::firstOrCreate(
                    [
                        'name' => $attributeName,
                        'product_type' => $storeTypeKey,
                    ],
                    [
                        'status' => 1,
                        'created_by' => 1,
                        'updated_by' => 1,
                        'created_at' => $currentTimestamp,
                        'updated_at' => $currentTimestamp,
                    ]
                );

                foreach ($values as $value) {
                    $data[] = [
                        'attribute_id' => $productAttribute->id,
                        'value' => $value,
                        'created_at' => $currentTimestamp,
                        'updated_at' => $currentTimestamp,
                    ];
                }
            }
        }

        ProductAttributeValue::insert($data);
    }


}
