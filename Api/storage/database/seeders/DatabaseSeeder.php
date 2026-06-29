<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // role & permission
        $this->call(ModelHasRolesSeeder::class);
        $this->call(PermissionAdminSeeder::class);
        $this->call(PermissionBranchSeeder::class);
        $this->call(PermissionDeliverymanSeeder::class);
        // location
        $this->call(LocationSeeder::class);
        $this->call(StoreAreaSeeder::class);
        // store
        $this->call(StoreSellerSeeder::class);
        $this->call(StoreSeeder::class);
        $this->call(StoreTypeSeeder::class);
        $this->call(StoreAreaSettingsSeeder::class);
        $this->call(StoreAreaSettingRangeChargeSeeder::class);
        $this->call(StoreAreaSettingStoreTypeSeeder::class);

        // customer and user
        $this->call(UserSeeder::class);
        $this->call(CustomerSeeder::class);
        // unit brand
        $this->call(BrandSeeder::class);
        $this->call(UnitSeeder::class);
        // Product
        $this->call(ProductAttributeSeeder::class);
        $this->call(ProductCategorySeeder::class);
        $this->call(ProductSeeder::class);
        // payment
        $this->call(PaymentGatewaySeeder::class);
        // subscription
        $this->call(SubscriptionPackageSeeder::class);
        // system commission
        $this->call(SystemCommissionSeeder::class);
        // wallet
        $this->call(WalletSeeder::class);
        // others
        $this->call(DepartmentSeeder::class);
//        $this->call(SliderSeeder::class);
        $this->call(BannerSeeder::class);
        $this->call(RolesSeeder::class);
        $this->call(CouponSeeder::class);
        $this->call(ReviewSeeder::class);
        $this->call(EmailTemplateSeeder::class);
        $this->call(MenuSeeder::class);
    }
}
