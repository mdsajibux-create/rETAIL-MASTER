<?php

namespace Modules\Pos\app\Services;


use App\Http\Resources\BranchDetailsPublicResource;
use App\Models\SystemCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Coupon\app\Models\CouponLine;
use Modules\Order\app\Models\Order;
use Modules\Order\app\Models\OrderDetail;
use Modules\Order\app\Transformers\InvoiceResource;
use Modules\Product\app\Models\FlashSale;
use Modules\Product\app\Models\Product;
use Modules\Product\app\Models\ProductVariant;
use Modules\Wallet\app\Models\Wallet;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class PosOrderService
{
    protected array $orderData, $packageData, $productData;

    public function __construct()
    {
    }

    private function formatAmount($value)
    {
        return shouldRound()
            ? round($value)       // Full integer
            : round($value, 2);   // 2 decimal places
    }

    public function placeOrder(Request $request)
    {
        // Step 1: Get Order Common Data
        $this->orderData = $this->getOrderData($request);

        // Step 2: Get Package Data
        $this->packageData = $this->getPackageData($request);

        // Step 3: Check stock before any DB writes
        $stockCheck = $this->checkStockQuantity();
        if ($stockCheck !== true) {
            return $stockCheck; // Fail early if stock insufficient
        }

        // Steps 4–9: Calculations (in-memory, no DB writes yet)
        $this->productData = $this->getProductDetails();
        $this->calculateFlashSale();
        $this->applyCoupon();
        $this->calculateTax();

        try {
            $order = null;
            DB::transaction(function () use (&$order) {

                // Step 11: Create Order
                $order = $this->createOrder($this->orderData);

                // Step 12: Create Order Details
                $this->createOrderDetail($order);

                // Step 13: Update payment status (wallet / cash)
                $this->paymentStatusUpdate($order);

                // Step 14: Update related entities after order placed
                $this->updateFlashSale();
                $this->updateCoupon();
                $this->updateProduct();
            }, 3); // Retry up to 3 times on deadlocks

            return response()->json([
                'message' => 'Order placed successfully.',
                'order_id' => $order->id,
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => 'Order failed. All changes have been rolled back.',
            ], 422);
        }
    }


    private function createOrder($order_data)
    {
        $branch_id = $order_data['branch_id'] ?? auth('api')->user()->branch_id;

        $orderAmount = $this->formatAmount(array_sum(array_column($this->packageData, 'line_total_price')));
        $couponDiscountAmountAdmin = $this->formatAmount(array_sum(array_column($this->packageData, 'coupon_discount_amount')));
        $flashDiscountAmountAdmin = $this->formatAmount(array_sum(array_column($this->packageData, 'admin_discount_amount')));
        $productDiscountAmount = $this->formatAmount(array_sum(array_column($this->packageData, 'discount_amount')));

        return Order::create([
            'branch_id' => $branch_id,
            'customer_id' => $order_data['customer_id'] ?? null,
            'order_type' => 'pos',
            'delivery_option' => 'self_pickup',
            'delivery_type' => 'immediate',
            'order_amount' => $orderAmount,
            'product_discount_amount' => $productDiscountAmount,
            'flash_discount_amount' => $flashDiscountAmountAdmin,
            'coupon_discount_amount' => $couponDiscountAmountAdmin,
            'payment_status' => 'pending',
            'payment_gateway' => $order_data['payment_gateway'] ?? null,
            'status' => 'pending'
        ]);
    }

    private function createOrderDetail($order = null)
    {
        $orderDetails = [];
        foreach ($this->packageData as $item) {
            $orderDetails[] = OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'],
                'behaviour' => $item['behaviour'],
                'product_sku' => $item['product_sku'],
                'variant_details' => $item['variant_details'],
                'product_campaign_id' => $item['campaign_id'] ?? null,
                'base_price' => $this->formatAmount($item['base_price']),
                'discount_type' => $item['admin_discount_type'],
                'discount_rate' => $this->formatAmount($item['admin_discount_rate']),
                'discount_amount' => $this->formatAmount($item['admin_discount_amount']),
                'price' => $this->formatAmount($item['price']),
                'quantity' => $item['quantity'],
                'line_total_price_with_qty' => $this->formatAmount($item['line_total_price_with_qty']),
                'coupon_discount_amount' => $this->formatAmount($item['coupon_discount_amount']),
                'line_total_excluding_tax' => $this->formatAmount($item['line_total_excluding_tax']),
                'tax_rate' => $this->formatAmount($item['tax_rate']),
                'tax_amount' => $this->formatAmount($item['tax_amount']),
                'total_tax_amount' => $this->formatAmount($item['total_tax_amount']),
                'line_total_price' => $this->formatAmount($item['line_total_price']),
            ]);
        }

        return $orderDetails;
    }


    private function getProductDetails()
    {
        $productDetails = [];

        foreach ($this->packageData as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) {
                return response()->json([
                    'message' => "Product not found for ID: {$item['product_id']}"
                ], 404);
            }

            $variant = ProductVariant::find($item['variant_id']);

            if (!$variant || (int)$variant->product_id !== (int)$product->id) {
                return response()->json([
                    'message' => "Variant not found for product: {$product->name}"
                ], 404);
            }
            $special = (float)($variant->special_price ?? 0);
            $regular = (float)($variant->price ?? 0);

            // Base price is special if available, otherwise regular
            $basePrice = $special > 0 ? $special : $regular;

            $discountAmount = 0;

            if ($regular > 0 && $special > 0 && $special < $regular) {
                // discount only if both exist and special < regular
                $discountAmount = $regular - $special;
            }
            $flashSale = $product->isInFlashDeal();

            $productDetails[] = [
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'behaviour' => $product->behaviour,
                'product_sku' => $variant->sku,
                'variant_details' => $variant->attributes,
                'base_price' => $basePrice,
                'regular_price' => $regular,
                'special_price' => $special,
                'discount_amount' => $discountAmount,
                'flash_sale' => $flashSale,
                'product_campaign_id' => $flashSale ? $flashSale['flash_sale_id'] : null,
                'admin_discount_type' => $flashSale ? $flashSale['discount_type'] : null,
                'admin_discount_rate' => $flashSale ? $flashSale['discount_amount'] : 0.00,
            ];
        }
        foreach ($this->packageData as $index => $item) {
            if (isset($productDetails[$index])) {
                $this->packageData[$index] = array_merge(
                    $item,
                    $productDetails[$index]
                );
            }
        }

        return $productDetails;
    }

    private function calculateTax()
    {
        // tax settings
        $tax = SystemCharge::first();
        $system_taxRate = $tax->order_tax ?? 0;

        $taxInfo = [];
        foreach ($this->packageData as $item) {
            $taxRate = (float)$system_taxRate;
            $taxAmount = ($item['price'] * $taxRate) / 100;
            $lineTotalExcludingTax = $this->formatAmount($item['price'] * $item['quantity'] - $item['coupon_discount_amount']);
            $totalTaxAmount = $this->formatAmount($taxAmount * $item['quantity']);
            $lineTotalPrice = $this->formatAmount($lineTotalExcludingTax + $totalTaxAmount);

            $taxInfo[] = [
                'line_total_excluding_tax' => $lineTotalExcludingTax,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_tax_amount' => $totalTaxAmount,
                'line_total_price' => $lineTotalPrice,
            ];
        }

        foreach ($this->packageData as $index => $item) {
            if (isset($taxInfo[$index])) {
                $this->packageData[$index] = array_merge(
                    $item,
                    $taxInfo[$index]
                );
            }
        }

        return $taxInfo;
    }

    private function calculateFlashSale()
    {
        $priceAfterFlashSaleDiscount = [];

        foreach ($this->packageData as $item) {
            $product = Product::find($item['product_id']);
            $variant = ProductVariant::find($item['variant_id']);
            $flashSale = $product->isInFlashDeal();

            $basePrice = $item['base_price'];
            $quantity = $item['quantity'];

            $discountAmount = 0.00;
            $price = $basePrice;
            $lineTotalPriceWithQty = $this->formatAmount($basePrice * $quantity);

            if ($flashSale) {
                $discountType = $flashSale['discount_type'] ?? null;
                $discountValue = $flashSale['discount_amount'] ?? 0;

                if ($discountType === 'percentage') {
                    $discountAmount = $this->formatAmount(($basePrice * $discountValue) / 100);
                } else {
                    $discountAmount = $this->formatAmount($discountValue);
                }

                $price = $this->formatAmount($basePrice - $discountAmount);
                $lineTotalPriceWithQty = $this->formatAmount($price * $quantity);
            }

            $priceAfterFlashSaleDiscount[] = [
                'admin_discount_amount' => $discountAmount,
                'price' => $price,
                'line_total_price_with_qty' => $lineTotalPriceWithQty,
            ];
        }

        foreach ($this->packageData as $index => $item) {
            if (isset($priceAfterFlashSaleDiscount[$index])) {
                $this->packageData[$index] = array_merge(
                    $item,
                    $priceAfterFlashSaleDiscount[$index]
                );
            }
        }

        return $priceAfterFlashSaleDiscount;
    }


    private function applyCoupon()
    {
        $couponInfo = [];
        $totalAmountForCoupon = $this->formatAmount(array_sum(array_column($this->packageData, 'line_total_price_with_qty')));

        $coupon = checkCoupon($this->orderData['coupon_code'], $totalAmountForCoupon);
        $couponTitle = $coupon['title'] ?? null;
        $couponCode = $coupon['code'] ?? null;

        $this->orderData = array_merge($this->orderData, [
            'coupon_title' => $couponTitle,
            'coupon_code' => $couponCode,
        ]);

        if (!empty($coupon)) {
            $remainingDiscount = $coupon['discounted_amount'];
            $distributedTotal = 0;
            $itemCount = count($this->packageData);

            foreach ($this->packageData as $index => $item) {
                $lineTotal = $item['line_total_price_with_qty'];
                if ($index === $itemCount - 1) {
                    $discount = $remainingDiscount - $distributedTotal;
                } else {
                    $discount = ($lineTotal / $totalAmountForCoupon) * $remainingDiscount;
                    $distributedTotal += $discount;
                }

                $couponInfo[] = [
                    'coupon_discount_amount' => $discount,
                ];
            }
        } else {
            foreach ($this->packageData as $index => $item) {
                $couponInfo[] = ['coupon_discount_amount' => 0];
            }
        }

        foreach ($this->packageData as $index => $item) {
            if (isset($couponInfo[$index])) {
                $this->packageData[$index] = array_merge(
                    $item,
                    $couponInfo[$index]
                );
            }
        }

        return $couponInfo;
    }


    private function orderSummary()
    {

    }

    private function getOrderData(Request $request)
    {
        return [
            'branch_id' => $request->branch_id ?? auth('api')->user()->branch_id,
            'customer_id' => $request->customer_id,
            'payment_gateway' => $request->payment_gateway,
            'coupon_code' => $request->coupon_code,
            'order_type' => 'pos',
            'delivery_option' => 'in_store',
            'delivery_type' => 'immediate',
        ];
    }

    private function getPackageData(Request $request)
    {
        $packages = $request->package;
        $items = [];

        foreach ($packages as $package) {
            foreach ($package['items'] as $item) {
                $items[] = [
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                ];
            }
        }

        return $items;
    }

    private function checkStockQuantity()
    {
        foreach ($this->packageData as $item) {
            $product = Product::find($item['product_id']);

            if (!$product) {
                return response()->json([
                    'message' => "Product not found for ID: {$item['product_id']}"
                ], 404);
            }

            $variant = ProductVariant::with('stock')->find($item['variant_id']);

            // Variant not found or doesn't belong to this product
            if (!$variant || (int)$variant->product_id !== (int)$product->id) {
                return response()->json([
                    'message' => "Variant not found for product: {$product->name}"
                ], 404);
            }

            // Check stock
            if ($variant?->stock?->qty < $item['quantity']) {
                return response()->json([
                    'message' => "Insufficient stock for product: {$product->name}, sku: {$variant->sku}"
                ], 422);
            }
        }

        return true;
    }


    private function paymentStatusUpdate($order = null): bool
    {
        if (!$order) {
            return false;
        }

        return DB::transaction(function () use ($order) {

            $paymentGateway = $this->orderData['payment_gateway'];
            $orderAmount = $order->order_amount;

            // Wallet payment flow
            if ($paymentGateway === 'wallet') {
                $customerWallet = Wallet::where('owner_id', $this->orderData['customer_id'])->lockForUpdate()->first();

                if (!$customerWallet || $customerWallet->balance < $orderAmount) {
                    return false;
                }

                $customerWallet->balance -= $orderAmount;
                $customerWallet->save();
            }

            $order->update(['payment_status' => 'paid']);

            $order->update([
                'payment_status' => 'paid',
                'status' => 'delivered',
            ]);

            return true;
        });
    }

    private function updateCoupon()
    {
        if (empty($this->orderData['coupon_code'])) {
            return false;
        }

        $coupon = CouponLine::where('coupon_code', $this->orderData['coupon_code'])->first();

        if (!$coupon) {
            return false;
        }

        // Prevent negative usage_limit
        if ($coupon->usage_limit > 0) {
            $coupon->update([
                'usage_count' => $coupon->usage_count + 1,
                'usage_limit' => max(0, $coupon->usage_limit - 1),
            ]);
        }

        return true;
    }

    private function updateFlashSale()
    {
        foreach ($this->packageData as $item) {
            if (empty($item['flash_sale']) || empty($item['product_campaign_id'])) {
                continue;
            }

            $flashSale = FlashSale::find($item['product_campaign_id']);

            if ($flashSale) {
                $quantity = max(0, (int)$item['quantity']);

                if ($quantity > 0) {
                    $flashSale->purchase_limit = max(0, $flashSale->purchase_limit - $quantity);
                    $flashSale->save();
                }
            }
        }

        return true;
    }


    private function updateProduct()
    {
        foreach ($this->packageData as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) {
                return response()->json([
                    'message' => "Product not found for ID: {$item['product_id']}"
                ], 404);
            }

            $variant = ProductVariant::with('stock')->find($item['variant_id']);

            if (!$variant || (int)$variant->product_id !== (int)$product->id) {
                return response()->json([
                    'message' => "Variant not found for product: {$product->name}"
                ], 404);
            }

            $quantity = (int)$item['quantity'];

            // Update the stock relation record if it exists
            if ($variant->stock) {
                $variant->stock->qty = max(0, $variant->stock->qty - $quantity);
                $variant->stock->save(); // Save stock
            }

            $variant->increment('order_count', (int)$item['quantity']);
            $product->increment('order_count', (int)$item['quantity']);
        }

        return true;
    }

    public function getInvoice($orderId)
    {
        $order_id = $orderId;
        $order = Order::with([
            'customer',
            'orderDetail',
            'deliveryman',
            'shippingAddress',
            'branch',
            'branch.zone',
            'branch.related_translations',
        ])
            ->where('id', $order_id)
            ->first();

        if (!$order) {
            return response()->json(['message' => __('messages.data_not_found')], 404);
        }

        $qrCode = $this->getQrCode($order_id);

        return response()->json([
            'invoice' => new InvoiceResource($order),
            'branch_details' => new BranchDetailsPublicResource($order->branch),
            'qr_code' => $qrCode,
        ]);
    }

    private function getQrCode($orderId)
    {
        $order = Order::findOrFail($orderId);

        $websiteName = config('app.frontend_url');
        $invoice = $order->invoice_number;

        $qrString = "OrderID: {$order->id}\nInvoice: #{$invoice}\nWebsite: {$websiteName}";

        $qrCode = base64_encode(
            QrCode::format('svg')->size(200)->generate($qrString)
        );

        return 'data:image/svg+xml;base64,' . $qrCode;
    }


}
