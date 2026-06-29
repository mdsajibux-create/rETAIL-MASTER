<?php

namespace App\Services;

use App\Helpers\ComHelper;
use App\Helpers\DeliveryChargeHelper;
use App\Models\CustomerAddress;
use App\Models\SystemCharge;
use App\Services\Order\OrderManageNotificationService;
use Modules\BusinessSettings\app\Models\ProductType;
use Modules\BusinessSettings\app\Models\Zone;
use Modules\Location\app\Models\Area;
use Modules\Location\app\Models\City;
use Modules\Location\app\Models\State;
use Modules\Order\app\Models\Order;
use Modules\Order\app\Models\OrderAddress;
use Modules\Order\app\Models\OrderDetail;
use Modules\Product\app\Models\Product;
use Modules\Product\app\Models\ProductStock;
use Modules\Product\app\Models\ProductVariant;
use PHPUnit\Event\Telemetry\System;

class OrderService
{
    protected $orderManageNotificationService;

    public function __construct(OrderManageNotificationService $orderManageNotificationService)
    {
        $this->orderManageNotificationService = $orderManageNotificationService;
    }

    public function createOrder($data)
    {
        $customer = auth()->guard('api_customer')->user();

        if (!$customer) {
            return false;
        }

        $customer_id   = $customer->id;
        $shouldRound   = shouldRound();
        $currencyData  = ComHelper::getCurrencyInfo($data['currency_code']);

        // ── FIX 3: fetch once, before any loop ─────────────────────────────────
        $systemCharge     = SystemCharge::latest()->first();
        $tax_disabled     = $systemCharge->order_include_tax_amount == 0;
        $global_tax_rate  = $tax_disabled ? 0 : $systemCharge->order_tax;
        $order_shipping_charge = $systemCharge->order_shipping_charge;

        // notes if admin google map enable zone wise delivery charge calculate || others wise country city and area select user

        // system charge settings
        $zone_system_enable = $systemCharge->zone_system_enable;

        // if google map enable
        $google_map_enabled = com_option_get('com_google_map_enable_disable') === 'on' || com_option_get('com_google_map_enable_disable') == 'on';
        $customer_latitude  = $data['customer_latitude'] ?? null;
        $customer_longitude = $data['customer_longitude'] ?? null;

        $deliveryOption = $data['packages'][0]['delivery_option'] ?? $data['delivery_option'] ?? 'home_delivery';

        // delivery option pickup in person
        if ($deliveryOption === 'takeaway'){
            $zone = null;
        }elseif ($google_map_enabled && $zone_system_enable === true){
            $allZones     = Zone::where('status', 1)->get();
            $detectedZone = null;

            foreach ($allZones as $z) {
                $geoJson = json_decode($z->coordinates->toJson(), true);

                $points = array_map(fn($coord) => [
                    'lat' => $coord[1],
                    'lng' => $coord[0],
                ], $geoJson['coordinates'][0]);

                if (pointInPolygon($customer_latitude, $customer_longitude, $points)) {
                    $detectedZone = $z;
                    break;
                }
            }

            $zone = $detectedZone ?? Zone::where('is_default', 1)->first();
            if (!$zone) {
                return false;
            }
        }else{
            $zone = CustomerAddress::find($data['shipping_address_id']);
        }

        // Eager-load all products + relations in one query
        $productIds = array_column($data['items'], 'product_id');
        $variantIds = array_column($data['items'], 'variant_id');

        $products = Product::with('variants', 'flashSaleProduct', 'flashSale')
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $variants = ProductVariant::whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        // ── PASS 1: validate stock and compute totalBasePrice ──────────────────
        $totalBasePrice = 0;

        foreach ($data['items'] as $itemData) {
            $product = $products[$itemData['product_id']] ?? null;
            $variant = $variants[$itemData['variant_id']] ?? null;

            if (!$product || !$variant) {
                return false;
            }

            // Stock check  Sum qty from product_stocks  all branches
            $totalStock = ProductStock::where('variant_id', $variant->id)->sum('qty');

            if ($totalStock < $itemData['quantity']) {
                return false;
            }

            if (isset($variant->price)) {
                $unitBasePrice    = ($variant->special_price !== null && $variant->special_price > 0)
                    ? $variant->special_price
                    : $variant->price;

                // FIX 2: extracted helper (see bottom of file)
                $discount         = $this->getFlashSaleDiscount($product, $unitBasePrice);
                $unitFinalPrice   = $unitBasePrice - $discount;
                $totalBasePrice  += $unitFinalPrice * $itemData['quantity'];
            }
        }

        // ── Coupon ─────────────────────────────────────────────────────────────
        $coupon_data = [
            'success'            => false,
            'coupon_code'        => null,
            'discount_amount'    => 0,
            'final_order_amount' => $totalBasePrice,
        ];

        if (!empty($data['coupon_code'])) {
            $applied = applyCoupon($data['coupon_code'], $totalBasePrice);
            if ($applied['success']) {
                $coupon_data = $applied;
            }
        }

        $coupon_discount = $coupon_data['success'] ? $coupon_data['discount_amount'] : 0;
        // ── Proportional coupon distribution per item start ──────────────────────────
        $itemCouponDiscounts = [];
        if ($coupon_discount > 0) {
            $totalForDistribution = 0;

            // Step A: calculate each item's after-flash line total
            foreach ($data['items'] as $itemData) {
                $variant   = $variants[$itemData['variant_id']];
                $product   = $products[$itemData['product_id']];
                $basePrice = ($variant->special_price !== null && $variant->special_price > 0)
                    ? $variant->special_price
                    : $variant->price;

                $flashDiscount = $this->getFlashSaleDiscount($product, $basePrice);
                $finalPrice    = $basePrice - $flashDiscount;
                $lineTotal     = $finalPrice * $itemData['quantity'];

                // Use variant_id as key (unique per item row)
                $itemCouponDiscounts[$itemData['variant_id']] = $lineTotal;
                $totalForDistribution += $lineTotal;
            }

            // Step B: convert line totals → proportional coupon share
            $distributedSoFar = 0;
            $lastIndex        = array_key_last($data['items']);

            foreach ($data['items'] as $index => $itemData) {
                $lineTotal = $itemCouponDiscounts[$itemData['variant_id']];

                if ($index === $lastIndex) {
                    // Last item gets exact remainder to avoid rounding drift
                    $itemCouponDiscounts[$itemData['variant_id']] = round($coupon_discount - $distributedSoFar, 2);
                } else {
                    $share = ($totalForDistribution > 0)
                        ? round(($lineTotal / $totalForDistribution) * $coupon_discount, 2)
                        : 0;
                    $itemCouponDiscounts[$itemData['variant_id']] = $share;
                    $distributedSoFar += $share;
                }
            }
        }
        // ──  coupon distribution per item end ──────────────────────────

        $productStocks = ProductStock::whereIn('variant_id', $variantIds)->get()->keyBy('variant_id');
        $branch_id = $productStocks->first()->branch_id ?? null;

        // ── Create Order ───────────────────────────────────────────────────────
        $order = Order::create([
            'branch_id'       => $branch_id,
            'customer_id'     => $customer_id,
            'zone_id'         => $zone->zone_id ?? null,
            'state_id'         => $zone->state_id ?? null,
            'city_id'         => $zone->city_id ?? null,
            'area_id'         => $zone->area_id ?? null,
            // FIX 6: simplified coupon condition
            'coupon_code'     => $coupon_data['success'] ? ($data['coupon_code'] ?? null) : null,
            'coupon_discount_amount' => $coupon_discount,
            'order_amount'    => 0,
            'payment_gateway' => $data['payment_gateway'],
            'payment_status'  => 'pending',
            'order_notes'     => $data['order_notes'] ?? null,
        ]);

        // ── Delivery address ───────────────────────────────────────────────────
        //  fallback to 'home_delivery'
        $deliveryOption = $data['packages'][0]['delivery_option'] ?? $data['delivery_option']  ?? 'home_delivery';

        if ($deliveryOption === 'takeaway') {
            OrderAddress::create([
                'order_id'       => $order->id,
                'zone_id'        => 0,
                'name'           => $data['name'] ?? null,
                'email'          => $data['email'] ?? null,
                'contact_number' => $data['contact_number'] ?? null,
                'type'           => 'others',
            ]);

            $zone = null;
        } else {

            // shipping_address_id must exist for home delivery
            if (!array_key_exists('shipping_address_id', $data)) {
                $order->delete();
                return false;
            }

            $customer_address = CustomerAddress::find($data['shipping_address_id'] ?? null);

            if (!$customer_address) {
                $order->delete();
                return false;
            }

            OrderAddress::create([
                'order_id'       => $order->id,
                'zone_id'        => $zone->zone_id ?? null,
                'type'           => $customer_address->type,
                'email'          => $customer_address->email,
                'contact_number' => $customer_address->contact_number,
                'address'        => $customer_address->address,
                'latitude'       => $customer_address->latitude,
                'longitude'      => $customer_address->longitude,
                'road'           => $customer_address->road,
                'house'          => $customer_address->house,
                'floor'          => $customer_address->floor,
                'postal_code'    => $customer_address->postal_code,
            ]);
        }

        // ── Shipping charge (computed once) ───────────────────────────────────
        if ($google_map_enabled && $zone_system_enable === true){
            $customer_lat  = $data['customer_latitude'] ?? null;
            $customer_lng  = $data['customer_longitude'] ?? null;
            $deliveryChargeData  = DeliveryChargeHelper::calculateDeliveryCharge($zone->id ?? null, $customer_lat, $customer_lng);
            $deliveryChargeData  = is_array($deliveryChargeData) ? $deliveryChargeData : ['delivery_charge' => null];
            $final_shipping_charge = (!empty($deliveryChargeData['delivery_charge'])) ? $deliveryChargeData['delivery_charge'] : $order_shipping_charge;
        } else {

            $area = Area::with('city.state')->find($zone->area_id ?? null);
            $city = null;
            $state = null;

            // if area empty
            if (!$area && !empty($zone->city_id)) {
                $city = City::with('state')->find($zone->city_id);
            }

            if (!$area && !$city && !empty($zone->state_id)) {
                $state = State::find($zone->state_id);
            }

            // Priority: area → city → state → system default
            $final_shipping_charge = $area?->delivery_charge
                ?? $city?->delivery_charge
                ?? $state?->delivery_charge
                ?? $systemCharge?->order_shipping_charge
                ?? 0;
        }

        //  guarded delivery_option access
        $delivery_option_value = $data['delivery_option'] ?? 'home_delivery';

        $order->update([
            'order_type'      => 'regular',
            'delivery_option' => $delivery_option_value,
            'delivery_type'   => 'standard',
            'delivery_time'   => $data['delivery_time'] ?? null,
            'shipping_charge' => $delivery_option_value === 'home_delivery' ? $final_shipping_charge : 0,
            'is_reviewed'     => false,
            'status'          => 'pending',
            'payment_status'  => 'pending',
        ]);

        // create OrderDetail rows ───────────────────────────────────
        $order_package_total_amount         = 0;
        $product_discount_amount_package    = 0;
        $flash_discount_amount              = 0;
        $item_amount_for_additional_charge  = 0;
        $order_additional_charge_name       = null;
        $order_additional_charge_amount     = 0;
        $order_tax_amount     = 0;

        foreach ($data['items'] as $itemData) {
            $product = $products[$itemData['product_id']];
            $variant = $variants[$itemData['variant_id']];

            // Base price
            $basePrice = ($variant->special_price !== null && $variant->special_price > 0)
                ? $variant->special_price
                : $variant->price;

            // on special_price for product discount
            $product_discount_amount = (
                $variant->special_price !== null &&
                $variant->special_price < $variant->price
            )
                ? ($variant->price - $variant->special_price) * $itemData['quantity']
                : 0;

            $product_discount_amount_package += $product_discount_amount;

            //  use extracted helper
            $flash_sale_admin_discount = 0;
            $product_flash_sale_id     = null;
            $flash_sale_discount_type  = null;
            $flash_sale_discount_rate  = 0;

            if (!empty($product->flashSale)) {
                $flashSale = $product->flashSale;
                $isExpired    = now()->gt($flashSale->end_time);
                $isInactive   = $flashSale->status == 0;
                $isOutOfLimit = $flashSale->purchase_limit == 0;

                if (!$isExpired && !$isInactive && !$isOutOfLimit) {
                    $product_flash_sale_id    = $flashSale->id;
                    $flash_sale_discount_type = $flashSale->discount_type;
                    $flash_sale_discount_rate = $flashSale->discount_amount;

                    $flash_sale_admin_discount = ($flashSale->discount_type === 'percentage')
                        ? ($basePrice * $flashSale->discount_amount / 100)
                        : $flashSale->discount_amount;

                    $flash_sale_admin_discount = $shouldRound
                        ? round($flash_sale_admin_discount)
                        : $flash_sale_admin_discount;
                }
            }

            // Final price after flash sale discount
            $finalPrice = $basePrice - $flash_sale_admin_discount;

             // Line total after flash sale (before tax, before coupon)
            $after_discount_final_price_with_qty = $finalPrice * $itemData['quantity'];

            // Tax
            $taxAmount       = $tax_disabled ? 0 : ($finalPrice / 100) * $global_tax_rate;
            $total_tax_amount = $taxAmount * $itemData['quantity'];

            $line_total_excluding_tax = $after_discount_final_price_with_qty;


            // Coupon share for this item (proportionally distributed)
            $item_coupon_discount = $itemCouponDiscounts[$itemData['variant_id']] ?? 0;

            // Final line total = subtotal + tax - coupon share
            $line_total_price = $shouldRound
                ? round($line_total_excluding_tax + $total_tax_amount - $item_coupon_discount)
                : round($line_total_excluding_tax + $total_tax_amount - $item_coupon_discount, 2);


            $orderDetails = OrderDetail::create([
                'order_id'                 => $order->id,
                'zone_id'                  => $order->zone_id,
                'product_id'               => $product->id,
                'product_sku'              => $variant->sku,
                'behaviour'                => $product->behaviour,
                'variant_id'               => $variant->id,
                'variant_details'          => $variant->attributes,
                'product_campaign_id'      => $product_flash_sale_id,
                'discount_type'            => $flash_sale_discount_type,
                'discount_rate'            => $flash_sale_discount_rate,
                'discount_amount'          => $flash_sale_admin_discount,
                'base_price'               => $basePrice,
                'price'                    => $finalPrice,
                'quantity'                 => $itemData['quantity'],
                'line_total_price_with_qty' => $after_discount_final_price_with_qty,
                // Coupon
                'coupon_discount_amount'  => $item_coupon_discount,
                // Tax
                'line_total_excluding_tax' => $line_total_excluding_tax,
                'tax_rate'                 => $global_tax_rate,
                'tax_amount'               => $taxAmount,
                'total_tax_amount'         => $total_tax_amount,
                // Final
                'line_total_price'         => $line_total_price,
            ]);


            // Decrement stock qty
            ProductStock::where('variant_id', $variant->id)->where('branch_id', $order->branch_id)->decrement('qty', $itemData['quantity']);


            $order_tax_amount += $orderDetails->tax_amount; // new add
            $order_package_total_amount += $orderDetails->line_total_price;
            $flash_discount_amount      += $flash_sale_admin_discount * $itemData['quantity'];
            $item_amount_for_additional_charge += $after_discount_final_price_with_qty;

            //  only decrement if flash sale was actually applied this item
            if ($product_flash_sale_id && $product->flashSale->purchase_limit >= $itemData['quantity']) {
                $product->flashSale->decrement('purchase_limit', $itemData['quantity']);
            }

            // Additional charge per product type
            $product_type_info = ProductType::where('type', $product->type)->first();

            // calculate additional fee
            if ($product_type_info) {
                // Accumulate charge names (e.g. "Furniture Charge, Flower Charge")
                if ($product_type_info->charge_name) {
                    $charge_names = array_filter(explode(', ', $order_additional_charge_name ?? ''));
                    if (!in_array($product_type_info->charge_name, $charge_names)) {
                        $charge_names[] = $product_type_info->charge_name;
                    }
                    $order_additional_charge_name = implode(', ', $charge_names);
                }

                // Use THIS item's subtotal only — not the running total
                $item_subtotal = $after_discount_final_price_with_qty;

                $item_additional_fee = $product_type_info->charge_type === 'percentage'
                    ? ($item_subtotal / 100) * $product_type_info->charge_amount
                    : $product_type_info->charge_amount; // fixed fee per item

                $item_additional_fee = $shouldRound
                    ? round($item_additional_fee)
                    : $item_additional_fee;

                //  Accumulate — don't assign
                $order_additional_charge_amount += $item_additional_fee;
            }

        } // end foreach items

        // AFTER (correct — applies coupon discount)
        $coupon_discount = $coupon_data['success'] ? $coupon_data['discount_amount'] : 0;

        // add shipping + additional charge ONCE, after the loop ──────
        $order->order_amount  = $order_package_total_amount + $order->shipping_charge + $order_additional_charge_amount - $coupon_discount;
        $order->product_discount_amount      = $product_discount_amount_package;
        $order->flash_discount_amount        = $flash_discount_amount;
        $order->tax_amount        = $order_tax_amount;
        $order->additional_charge_name = $order_additional_charge_name;
        $order->additional_charge_amount = $order_additional_charge_amount;
        $order->save();

        // decrement stock qty login add

        // ── Return & notify ────────────────────────────────────────────────────
        $order = Order::with('orderAddress', 'customer')->where('id', $order->id)->first();
        $order_ids = $order->pluck('id')->toArray();

        $this->orderManageNotificationService->createOrderNotification($order_ids, 'new-order');

        return [
            $order,
            'customer' => $customer,
        ];
    }

    /**
     * FIX 2: centralised flash sale discount calculator.
     * Returns the discount amount to subtract from $basePrice.
     * Returns 0 if sale is expired, inactive, or exhausted.
     */
    private function getFlashSaleDiscount(Product $product, float $basePrice): float
    {
        if (empty($product->flashSale) || !isset($product->flashSale->discount_amount)) {
            return 0;
        }

        $flashSale    = $product->flashSale;
        $isExpired    = now()->gt($flashSale->end_time);
        $isInactive   = $flashSale->status == 0;
        $isOutOfLimit = $flashSale->purchase_limit == 0;

        if ($isExpired || $isInactive || $isOutOfLimit) {
            return 0;
        }

        $discount = ($flashSale->discount_type === 'percentage')
            ? ($basePrice * $flashSale->discount_amount / 100)
            : $flashSale->discount_amount;

        return shouldRound() ? round($discount) : $discount;
    }


    public function updateOrderStatus($orderId, $status)
    {
        $order = Order::findOrFail($orderId);
        $order->status = $status;
        $order->save();
        return $order;
    }

    public function getOrderDetails($orderId)
    {
        $order = Order::with(['orderItems', 'sellerStore'])->findOrFail($orderId);
        return $order;
    }


}
