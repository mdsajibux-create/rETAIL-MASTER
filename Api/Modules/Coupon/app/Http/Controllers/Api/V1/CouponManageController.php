<?php

namespace Modules\Coupon\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\CouponLineRequest;
use App\Http\Requests\CouponRequest;
use App\Http\Resources\Com\PaginationResource;
use App\Http\Resources\CouponLineResource;
use App\Interfaces\CouponApplyManageInterface;
use App\Repositories\CouponLineManageRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Coupon\app\Models\Coupon;

class CouponManageController extends Controller
{
    public function __construct(protected CouponApplyManageInterface $couponRepo, protected CouponLineManageRepository $couponLineRepo)
    {
    }

    public function listCoupons(Request $request)
    {
        return $this->couponRepo->getPaginatedCoupon(
            $request->limit ?? 10,
            $request->page ?? 1,
            $request->language ?? DEFAULT_LANGUAGE,
            $request->search ?? "",
            $request->sortField ?? 'id',
            $request->sort ?? 'asc',
            []
        );
    }

    public function createCoupon(CouponRequest $request): JsonResponse
    {
        $coupon = $this->couponRepo->store($request->all());

        createOrUpdateTranslation(
            $request,
            $coupon,
            'Modules\Coupon\app\Models\Coupon',
            $this->couponRepo->translationKeys()
        );

        if ($coupon) {
            return $this->success(translate('messages.save_success', ['name' => 'Coupon']));
        } else {
            return $this->failed(translate('messages.save_failed', ['name' => 'Coupon']));
        }
    }

    public function updateCoupon(CouponRequest $request)
    {
        $coupon = $this->couponRepo->update($request->all());
        createOrUpdateTranslation($request, $coupon, 'Modules\Coupon\app\Models\Coupon', $this->couponRepo->translationKeys());
        if ($coupon) {
            return $this->success(translate('messages.update_success', ['name' => 'Coupon']));
        } else {
            return $this->failed(translate('messages.update_failed', ['name' => 'Coupon']));
        }
    }

    public function getCouponById(Request $request)
    {
        return $this->couponRepo->getCouponById($request->id);
    }

    public function deleteCoupon($id)
    {
        $this->couponRepo->delete($id);
        return $this->success(translate('messages.delete_success', ['name' => 'Coupon']));
    }

    public function changeCouponStatus(Request $request)
    {
        $coupon = Coupon::findOrFail($request->id);
        $coupon->status = !$coupon->status;
        $coupon->save();
        return $this->success(translate('messages.update_success', ['name' => 'Coupon']));
    }

    public function couponWiseLine(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coupon_id' => 'required|exists:coupons,id',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        return $this->couponRepo->coupon_wise_coupon_line($request->coupon_id);
    }

    public function listCouponLines(Request $request)
    {
        $couponLines = $this->couponLineRepo->getPaginatedCouponLines(
            $request->limit ?? 10,
            $request->page ?? 1,
            $request->search ?? "",
            $request->sortField ?? 'id',
            $request->sort ?? 'asc',
            $request->filters ?? []
        );

        return response()->json([
            'coupon_lines' => CouponLineResource::collection($couponLines),
            'meta' => new PaginationResource($couponLines),
        ]);
    }

    public function createCouponLine(CouponLineRequest $request): JsonResponse
    {
        $discount_type = $request->get('discount_type');
        $discount_amount = $request->get('discount');
        $shouldRound = shouldRound();
        if ($shouldRound && $discount_type === 'amount' && is_float($discount_amount)) {
            return response()->json([
                'message' => __('messages.should_round', ['name' => 'Discount']),
            ]);
        }
        if (!isset($request->coupon_code)) {
            $request['coupon_code'] = generateRandomCouponCode();
        }
        $couponLine = $this->couponLineRepo->couponLineStore($request->all());

        if ($couponLine) {
            return $this->success(translate('messages.save_success', ['name' => 'Coupon Line']));
        } else {
            return $this->failed(translate('messages.save_failed', ['name' => 'Coupon Line']));
        }
    }

    public function updateCouponLine(CouponLineRequest $request)
    {
        $discount_type = $request->get('discount_type');
        $discount_amount = $request->get('discount');
        $shouldRound = shouldRound();
        if ($shouldRound && $discount_type === 'amount' && is_float($discount_amount)) {
            return response()->json([
                'message' => __('messages.should_round', ['name' => 'Discount']),
            ]);
        }
        $couponLine = $this->couponLineRepo->couponLineUpdate($request->all());
        if ($couponLine) {
            return $this->success(translate('messages.update_success', ['name' => 'Coupon Line']));
        } else {
            return $this->failed(translate('messages.update_failed', ['name' => 'Coupon']));
        }
    }

    public function getCouponLineById(Request $request)
    {
        $couponLine = $this->couponLineRepo->getCouponLineById($request->id);
        return response()->json(new CouponLineResource($couponLine));
    }

    public function deleteCouponLine($id)
    {
        $this->couponLineRepo->couponLineDelete($id);
        return $this->success(translate('messages.delete_success', ['name' => 'Coupon Line']));
    }
}
