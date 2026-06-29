<?php

namespace App\Repositories;

use App\Interfaces\OrderRefundInterface;
use App\Models\Translation;
use Illuminate\Http\Request;
use Modules\Order\app\Models\Order;
use Modules\Order\app\Models\OrderMaster;
use Modules\Order\app\Models\OrderRefund;
use Modules\Order\app\Models\OrderRefundReason;

class OrderRefundRepository implements OrderRefundInterface
{
    public function __construct(protected OrderRefundReason $refundReason, protected Translation $translation)
    {

    }

    public function translationKeys(): mixed
    {
        return $this->refundReason->translationKeys;
    }

    public function get_order_refund_request(array $filters)
    {
        $query = OrderRefund::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereHas('customer', function ($q) use ($filters) {
                    $q->where('first_name', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('last_name', 'like', '%' . $filters['search'] . '%');
                })
                    ->orWhereHas('order', function ($q) use ($filters) {
                        $q->where('invoice_number', 'like', '%' . $filters['search'] . '%');
                    });
            });
        }

        if (isset($filters['order_refund_reason_id'])) {
            $query->where('order_refund_reason_id', $filters['order_refund_reason_id']);
        }

        if (isset($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        return $query->with([
            'order',
            'customer',
            'orderRefundReason.related_translations'
        ])->latest()
          ->paginate($filters['per_page'] ?? 10);
    }

    public function get_branch_order_refund_request(int $branch_id, array $filters)
    {
        $query = OrderRefund::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereHas('customer', function ($q) use ($filters) {
                    $q->where('first_name', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('last_name', 'like', '%' . $filters['search'] . '%');
                })
                    ->orWhereHas('order', function ($q) use ($filters) {
                        $q->where('invoice_number', 'like', '%' . $filters['search'] . '%');
                    });
            });
        }

        if (isset($filters['order_refund_reason_id'])) {
            $query->where('order_refund_reason_id', $filters['order_refund_reason_id']);
        }

        return $query->where('branch_id', $branch_id)
            ->with([
                'customer',
                'orderRefundReason'
            ])
            ->latest()
            ->paginate($filters['per_page'] ?? 10);
    }

    public function create_order_refund_request(int $order_id, array $data)
    {
        if (!$order_id) {
            return false;
        }

        if (!auth('api_customer')->check()) {
            unauthorized_response();
        }

        $customer = auth('api_customer')->user();
        $order = Order::find($order_id);

        if ($order->status !== 'delivered') {
            return 'not_delivered';
        }

        $alreadyRequested = OrderRefund::where('order_id', $order_id)->whereIn('status', ['pending', 'refunded'])->exists();

        if ($alreadyRequested) {
            return 'already_requested_for_refund';
        }

        $success = OrderRefund::create([
            'order_id' => $order_id,
            'customer_id' => $customer->id,
            'branch_id' => $order->branch_id,
            'order_refund_reason_id' => $data['order_refund_reason_id'],
            'customer_note' => $data['customer_note'],
            'file' => $data['file'] ?? null,
            'status' => 'pending',
            'amount' => $order->order_amount,
        ]);

        if ($success) {
            $order->refund_status = 'requested';
            $order->save();
            return true;
        } else {
            return false;
        }
    }

    public function approve_refund_request(int $id, string $status)
    {
        $request = OrderRefund::find($id);

        if (!$request) {
            return false;
        }

        $request->update(['status' => $status]);

        // Use Eloquent to trigger observer
        $order = Order::find($request->order_id);
        if (!$order) {
            return false;
        }

        $order->refund_status = 'processing';
        return $order->save(); //  this triggers the observer

    }

    public function reject_refund_request(int $id, string $status, string $reason)
    {
        $request = OrderRefund::find($id);

        if (!$request) {
            return false;
        }

        $request->update([
            'status' => $status,
            'reject_reason' => $reason
        ]);

        // Use Eloquent to trigger observer
        $order = Order::find($request->order_id);
        if (!$order) {
            return false;
        }

        $order->refund_status = 'rejected';
        return $order->save(); //  this triggers the observer
    }

    public function refunded_refund_request(int $id, string $status)
    {
        $request = OrderRefund::find($id);

        if (!$request) {
            return false;
        }

        $request->update(['status' => $status]);

        // Use Eloquent to trigger observer
        $order = Order::find($request->order_id);
        if (!$order) {
            return false;
        }

        $order->refund_status = 'refunded';
        return $order->save(); //  this triggers the observer
    }


    public function order_refund_reason_list(array $filters)
    {
        $query = OrderRefundReason::query();
        if (isset($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('reason', 'LIKE', $searchTerm)
                    ->orWhereHas('related_translations', function ($q) use ($searchTerm) {
                        $q->whereIn('key', ['reason'])
                            ->where('value', 'LIKE', $searchTerm);
                    });
            });
        }
        $perPage = $filters['per_page'] ?? 10;
        return $query->with('related_translations')->paginate($perPage);
    }

    public function create_order_refund_reason(string $reason)
    {
        if ($reason) {
            $reason = OrderRefundReason::create([
                'reason' => $reason
            ]);
            return $reason->id;
        } else {
            return false;
        }
    }

    public function update_order_refund_reason(array $data)
    {
        $reason = OrderRefundReason::find($data['id']);
        if ($reason) {
            $reason->update([
                'reason' => $data['reason']
            ]);
            return $reason->id;
        } else {
            return false;
        }
    }

    public function get_order_refund_reason_by_id(int $id)
    {
        $reason = OrderRefundReason::with('related_translations')->find($id);
        if (!$reason) {
            return false;
        }
        return $reason;
    }


    public function createOrUpdateTranslation(Request $request, int|string $refid, string $refPath, array $colNames): bool
    {
        if (empty($request['translations'])) {
            return false;
        }

        $requestedLanguages = array_column($request['translations'], 'language_code');

        // Delete translations for languages not present in the request
        $this->translation->where('translatable_type', $refPath)
            ->where('translatable_id', $refid)
            ->whereNotIn('language', $requestedLanguages)
            ->delete();

        $translations = [];
        foreach ($request['translations'] as $translation) {
            foreach ($colNames as $key) {
                $translatedValue = $translation[$key] ?? null;

                if ($translatedValue === null) {
                    continue;
                }

                $trans = $this->translation
                    ->where('translatable_type', $refPath)
                    ->where('translatable_id', $refid)
                    ->where('language', $translation['language_code'])
                    ->where('key', $key)
                    ->first();

                if ($trans) {
                    $trans->value = $translatedValue;
                    $trans->save();
                } else {
                    $translations[] = [
                        'translatable_type' => $refPath,
                        'translatable_id' => $refid,
                        'language' => $translation['language_code'],
                        'key' => $key,
                        'value' => $translatedValue,
                    ];
                }
            }
        }

        if (!empty($translations)) {
            $this->translation->insert($translations);
        }

        return true;
    }


    public function delete_order_refund_reason(int $id)
    {
        $reason = OrderRefundReason::find($id);
        if ($reason) {
            // Delete related translations
            $this->translation->where('translatable_type', OrderRefundReason::class)
                ->where('translatable_id', $id)
                ->delete();

            // Delete the refund reason
            $reason->delete();

            return true;
        } else {
            return false;
        }
    }
}