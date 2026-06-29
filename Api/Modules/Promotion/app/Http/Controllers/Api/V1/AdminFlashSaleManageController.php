<?php

namespace Modules\Promotion\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\FlashDealProductRequest;
use App\Http\Requests\FlashSaleRequest;
use App\Http\Resources\Admin\AdminFlashSaleDetailsResource;
use App\Http\Resources\Admin\AdminFlashSaleDropdownResource;
use App\Http\Resources\Admin\AdminFlashSaleProductResource;
use App\Http\Resources\Admin\AdminFlashSaleResource;
use App\Http\Resources\Com\PaginationResource;
use App\Services\FlashSaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Product\app\Models\FlashSale;

class AdminFlashSaleManageController extends Controller
{
    protected $flashSaleService;

    public function __construct(FlashSaleService $flashSaleService)
    {
        $this->flashSaleService = $flashSaleService;
    }

    public function createFlashSale(FlashSaleRequest $request): JsonResponse
    {
        $discount_type = $request->get('discount_type');
        $discount_amount = $request->get('discount_amount');
        $shouldRound = shouldRound();

        if ($shouldRound && $discount_type === 'amount' && is_float($discount_amount)) {
            return response()->json([
                'message' => __('messages.should_round', ['name' => 'Discount amount']),
            ]);
        }

        // store
        $flashSale = $this->flashSaleService->createFlashSale($request->validated());

        // languages create
        try {
            createOrUpdateTranslation($request, $flashSale, 'Modules\Product\Models\FlashSale', $this->flashSaleService->translationKeys());
        }catch (\Exception $exception){}

        if ($flashSale) {
            return response()->json([
                'status' => true,
                'status_code' => 201,
                'message' => __('messages.save_success', ['name' => 'Flash Sale']),
            ], 201);
        } else {
            return response()->json([
                'status' => false,
                'status_code' => 400,
            ]);
        }
    }

    public function updateFlashSale(FlashSaleRequest $request): JsonResponse
    {
        $discount_type = $request->get('discount_type');
        $discount_amount = $request->get('discount_amount');
        $shouldRound = shouldRound();
        if ($shouldRound && $discount_type === 'amount' && is_float($discount_amount)) {
            return response()->json([
                'message' => __('messages.should_round', ['name' => 'Discount amount']),
            ]);
        }

        $flashSale = $this->flashSaleService->updateFlashSale($request->all());

        createOrUpdateTranslation(
            $request,
            $flashSale,
            'Modules\Product\Models\FlashSale',
            $this->flashSaleService->translationKeys()
        );

        if ($flashSale) {
            return response()->json([
                'success' => true,
                'message' => __('messages.update_success', ['name' => 'Flash Sale']),
            ]);
        } else {
            return response()->json([
                'status' => false,
                'status_code' => 400,
            ]);
        }
    }

    public function listFlashSales(Request $request)
    {
        $filters = [
            "title" => $request->title,
            "start_date" => $request->start_date,
            "end_date" => $request->end_date,
            "per_page" => $request->per_page,
        ];
        $flashSales = $this->flashSaleService->getAdminFlashSales($filters);
        return response()->json([
                'data' => AdminFlashSaleResource::collection($flashSales),
                'meta' => new PaginationResource($flashSales)
            ]
        );
    }

    public function listAllFlashSaleProducts(Request $request)
    {
        $filters = [
            'flash_sale_id' => $request->flash_sale_id,
            'status' => $request->status,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'per_page' => $request->per_page,
        ];

        $flashSaleProducts = $this->flashSaleService->getAllFlashSaleProducts($filters);

        if ($flashSaleProducts) {
            return response()->json([
                'data' => AdminFlashSaleProductResource::collection($flashSaleProducts),
                'meta' => new PaginationResource($flashSaleProducts)
            ], 200);
        } else {
            return response()->json([
                'message' => __('messages.data_not_found')
            ], 404);
        }
    }

    public function adminAddProductToFlashSale(FlashDealProductRequest $request)
    {
        // check if the products are already in flash sale
        $existingProducts = $this->flashSaleService->getExistingFlashSaleProducts($request->products);

        if ($existingProducts) {
            return response()->json($existingProducts);
        }

        $data = $this->flashSaleService->associateProductsToFlashSale($request->flash_sale_id, $request->products);

        if ($data) {
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.request_success', ['name' => 'Products'])
            ]);
        } else {
            return response()->json([
                'status' => false,
                'status_code' => 400,
                'message' => __('messages.request_failed', ['name' => 'Products'])
            ]);
        }
    }

    public function adminUpdateProductToFlashSale(FlashDealProductRequest $request)
    {
        // store
        $data = $this->flashSaleService->updateFlashSaleProducts(
            $request->flash_sale_id,
            $request->products,
        );

        if ($data) {
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.update_success', ['name' => 'Products'])
            ]);
        } else {
            return response()->json([
                'status' => false,
                'status_code' => 400,
                'message' => __('messages.update_failed', ['name' => 'Products'])
            ]);
        }
    }

    public function getFlashSaleById(Request $request)
    {
        $flashSales = $this->flashSaleService->getFlashSaleById($request->id);

        if ($flashSales) {
            return response()->json(new AdminFlashSaleDetailsResource($flashSales));
        } else {
            return response()->json([
                'message' => __('messages.data_not_found')
            ], 404);
        }

    }

    public function getFlashSaleDropdown()
    {
        $flashSale = $this->flashSaleService->getAdminFlashSalesDropdown();
        if ($flashSale) {
            return response()->json(AdminFlashSaleDropdownResource::collection($flashSale));
        } else {
            return response()->json([
                'message' => __('messages.data_not_found')
            ], 404);
        }
    }

    public function changeFlashSaleStatus(Request $request)
    {
        $id = $request->id;
        $flashSale = FlashSale::find($id);

        if (empty($flashSale)){
            return response()->json([
                'status' => false,
                'message' => __('messages.not_found', ['name' => 'Flash Sale']),
            ]);
        }

        $updated = $flashSale->update([
            'status' => !((bool) $flashSale->status)
        ]);

        if ($updated) {
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.update_success', ['name' => 'Flash sale status']),

            ]);
        } else {
            return response()->json([
                'status' => false,
                'status_code' => 400,
            ]);
        }
    }

    public function deleteFlashSale($id)
    {
       $data = FlashSale::find($id);

       if (empty($data)){
           return response()->json([
               'status' => false,
               'status_code' => __('messages.not_found', ['name' => 'Flash sale']),
           ]);
       }

        $flashSale = $this->flashSaleService->deleteFlashSale($id);
        $flashSaleProducts = $this->flashSaleService->deleteFlashSaleProducts($id);

        if ($flashSale && $flashSaleProducts) {
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.delete_success', ['name' => 'Flash sale']),
            ]);
        } else {
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.delete_failed', ['name' => 'Flash sale']),
            ]);
        }

    }

    public function deactivateFlashSale()
    {
        $success = $this->flashSaleService->deactivateExpiredFlashSales();

        if ($success) {
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.update_success', ['name' => 'Flash sale status']),
            ]);
        } else {
            return response()->json([
                'status' => false,
                'status_code' => 400,
                'message' => __('messages.update_failed', ['name' => 'Flash sale status'])
            ]);
        }
    }


}
