<?php

namespace Modules\Analytics\app\Http\Controllers\Api\V1;

use App\Exports\OrderReportExport;
use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Admin\AdminOrderDashboardReportResource;
use App\Http\Resources\Admin\AdminOrderReportResource;
use App\Http\Resources\Admin\AdminTransactionDashboardReportResource;
use App\Http\Resources\Admin\AdminTransactionReportResource;
use App\Http\Resources\Com\PaginationResource;
use App\Interfaces\AdminDashboardInterface;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Order\app\Models\Order;
use Modules\Order\app\Models\OrderDetail;

class AdminReportAnalyticsManageController extends Controller
{
    public function __construct(protected AdminDashboardInterface $adminRepo)
    {

    }

    public function reportList(Request $request)
    {
        $reports = [
            'transaction_report' => 'Transaction Report Data',
            'item_report' => 'Item Report Data',
            'expense_report' => 'Expense Report Data',
            'disbursement_report' => 'Disbursement Report Data',
            'order_report' => 'Order Report Data',
        ];

        // Optional: Filter by report type if specified in the request
        $reportType = $request->get('type');

        if ($reportType && isset($reports[$reportType])) {
            return response()->json([
                'message' => ucfirst(str_replace('_', ' ', $reportType)),
                'data' => $reports[$reportType],
            ]);
        }

        // Default: Return all reports
        return response()->json([
            'message' => 'Admin Report Analytics Index',
            'reports' => array_keys($reports),
        ]);
    }

    public function orderReport(Request $request)
    {
        $filters = [
            'zone_id' => $request->zone_id,
            'payment_gateway' => $request->payment_gateway,
            'payment_status' => $request->payment_status,
            'order_status' => $request->order_status,
            'customer_id' => $request->customer_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'search' => $request->search,
            'per_page' => $request->per_page
        ];

        $query = OrderDetail::query();

        if (isset($filters['search'])) {
            $query->whereHas('order', function ($q) use ($filters) {
                $q->where('id', 'LIKE', '%' . $filters['search'] . '%')
                    ->orWhere('invoice_number', 'LIKE', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['zone_id'])) {
            $query->where('zone_id', $filters['zone_id']);
        }

        if (isset($filters['payment_gateway'])) {
            $query->whereHas('order', function ($q) use ($filters) {
                $q->where('payment_gateway', $filters['payment_gateway']);
            });
        }

        if (isset($filters['payment_status'])) {
            $query->whereHas('order', function ($q) use ($filters) {
                $q->where('payment_status', $filters['payment_status']);
            });
        }

        if (isset($filters['order_status'])) {
            $query->whereHas('order', function ($q) use ($filters) {
                $q->where('status', $filters['order_status']);
            });
        }


        if (isset($filters['customer_id'])) {
            $query->whereHas('order.orderMaster', function ($q) use ($filters) {
                $q->where('customer_id', $filters['customer_id']);
            });
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereHas('order', function ($q) use ($filters) {
                $q->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
            });
        } elseif (isset($filters['start_date'])) {
            $query->whereHas('order', function ($q) use ($filters) {
                $q->whereDate('created_at', '>=', $filters['start_date']);
            });
        } elseif (isset($filters['end_date'])) {
            $query->whereHas('order', function ($q) use ($filters) {
                $q->whereDate('created_at', '<=', $filters['end_date']);
            });
        }
        $orderDetails = $query->with([
            'order.customer',
            'order',
            'order.zone',
            'order.state',
            'order.city',
            'order.area'
        ])
            ->latest()
            ->paginate($filters['per_page'] ?? 20);

        $dashboard = $this->adminRepo->getSummaryDataWithFilters($filters);
        if ($request->has('export') && in_array($request->export, ['csv', 'xlsx'])) {
            return Excel::download(new OrderReportExport($orderDetails), 'order_report_' . time() . '.' . $request->export);
        }

        return response()->json([
            'dashboard' => new AdminOrderDashboardReportResource((object)$dashboard),
            'data' => AdminOrderReportResource::collection($orderDetails),
            'meta' => new PaginationResource($orderDetails)
        ]);
    }

    public function transactionReport(Request $request)
    {

        $filters = [
            'zone_id' => $request->zone_id,
            'customer_id' => $request->customer_id,
            'payment_gateway' => $request->payment_gateway,
            'payment_status' => $request->payment_status,
            'order_status' => $request->order_status,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'search' => $request->search,
            'per_page' => $request->per_page
        ];

            $query = Order::with([
                'customer',
                'orderDetail',
                'zone',
                'state',
                'city',
                'area',
            ]);

            if (isset($filters['search'])) {
                $query->where('id', 'LIKE', '%' . $filters['search'] . '%')
                    ->orWhere('invoice_number', 'LIKE', '%' . $filters['search'] . '%');
            }

            if (isset($filters['zone_id'])) {
                $query->where('zone_id', $filters['zone_id']);
            }

            if (isset($filters['payment_gateway'])) {
                $query->where('payment_gateway', $filters['payment_gateway']);
            }

            if (isset($filters['payment_status'])) {
                $query->where('payment_status', $filters['payment_status']);
            }

            if (isset($filters['order_status'])) {
                $query->where('status', $filters['order_status']);
            }

            if (isset($filters['customer_id'])) {
                $query->where('customer_id', $filters['customer_id']);
            }

            if (isset($filters['start_date']) && isset($filters['end_date'])) {
                $query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
            } elseif (isset($filters['start_date'])) {
                $query->whereDate('created_at', '>=', $filters['start_date']);
            } elseif (isset($filters['end_date'])) {
                $query->whereDate('created_at', '<=', $filters['end_date']);
            }

            $filteredQuery = (clone $query);
            $orderDetails = $query
                ->latest()
                ->paginate($filters['per_page'] ?? 20);

            if ($request->has('export') && in_array($request->export, ['csv', 'xlsx'])) {
                return Excel::download(new OrderReportExport($orderDetails), 'order_report_' . time() . '.' . $request->export);
            }

            return response()->json([
                'dashboard' => new AdminTransactionDashboardReportResource($filteredQuery),
                'data' => AdminTransactionReportResource::collection($orderDetails),
                'meta' => new PaginationResource($orderDetails)
            ]);

    }

}
