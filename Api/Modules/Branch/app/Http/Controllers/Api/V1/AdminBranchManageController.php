<?php

namespace Modules\Branch\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\AdminBranchRequest;
use App\Http\Requests\BranchStoreRequest;
use App\Http\Resources\Com\BranchListResource;
use App\Http\Resources\Com\PaginationResource;
use App\Interfaces\BranchManageInterface;
use App\Services\TrashService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Branch\app\Models\Branch;

class AdminBranchManageController extends Controller
{
    private $trashService;

    public function __construct(protected BranchManageInterface $storeRepo, TrashService $trashService)
    {
        $this->trashService = $trashService;
    }

    public function branches(Request $request)
    {
        $stores = $this->storeRepo->branchList(
            $request->per_page ?? 10,
            $request->status ?? "",
            $request->page ?? 1,
            $request->language ?? DEFAULT_LANGUAGE,
            $request->search ?? "",
            $request->sortField ?? 'id',
            $request->sort ?? 'asc',
            []
        );

        return response()->json([
            'data' => BranchListResource::collection($stores),
            'meta' => new PaginationResource($stores),
        ]);
    }

    public function createBranch(AdminBranchRequest $request): JsonResponse
    {
        $request['status'] = 1;
        $store = $this->storeRepo->store($request->all());

        createOrUpdateTranslation($request, $store, 'Modules\Branch\app\Models\Branch', $this->storeRepo->translationKeys());

        if ($store) {
            return response()->json([
                'message' => __('messages.save_success', ['name' => 'Branch']),
            ],201);
        } else {
            return response()->json([
                'message' => __('messages.save_failed', ['name' => 'Branch']),
            ],500);
        }
    }

    public function updateBranch(BranchStoreRequest $request)
    {
        $branch_id = $this->storeRepo->update($request->all());

        if (!$branch_id) {
            return response()->json([
                'message' => __('messages.update_failed', ['name' => 'Branch']),
            ], 500);
        }

        createOrUpdateTranslation(
            $request,
            $branch_id,
            'Modules\Branch\app\Models\Branch',
            $this->storeRepo->translationKeys()
        );

        if ($branch_id) {
            return response()->json([
                'message' => __('messages.update_success', ['name' => 'Branch']),
            ],200);
        } else {
            return response()->json([
                'message' => __('messages.save_failed', ['name' => 'Branch']),
            ],500);
        }
    }

    public function getBranchById(Request $request)
    {
        return $this->storeRepo->getBranchById($request->id);
    }


    public function deletedBranchRecords()
    {
        $records = $this->storeRepo->records(true);

        return response()->json([
            "data" => $records,
            "massage" => "Records were restored successfully!"
        ], 201);
    }

    public function deleteBranch($id)
    {
        if (runningOrderExists($id)) {
            return response()->json([
                'message' => __('messages.has_running_orders', ['name' => 'Branch'])
            ]);
        }
        $this->storeRepo->delete($id);
        return $this->success(translate('messages.delete_success'));
    }


    public function changeBranchStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:branches,id',
            'status' => 'required|in:0,1,2',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $success = $this->storeRepo->changeStatus($request->all());

        if ($success) {
            return $this->success(__('messages.update_success', ['name' => 'Branch status']));
        } else {
            return $this->failed(__('messages.update_failed', ['name' => 'Branch status']));
        }
    }

    public function setWebBranch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        // First reset all branches
        Branch::whereNull('deleted_at')->update([
            'is_web' => false,
        ]);

        // Then set selected branch as web branch
        $branch = Branch::where('id', $request->id)
            ->where('deleted_at', null)
            ->update([
                'is_web' => true,
            ]);

        if ($branch) {
            return $this->success(__('messages.update_success', ['name' => 'Web branch']));
        } else {
            return $this->failed(__('messages.update_failed', ['name' => 'Web branch']));
        }
    }

    public function branchTrashList(Request $request)
    {
        $trash = $this->trashService->listTrashed('branch', $request->per_page ?? 10);

        return response()->json([
            'data' => BranchListResource::collection($trash),
            'meta' => new PaginationResource($trash)
        ]);
    }

    public function restoreStoreTrashed(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'ids'   => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        $ids = $request->ids;
        $restored = $this->trashService->restore('branch', $ids);

        return response()->json([
            'message' => __('messages.restore_success', ['name' => 'Branch']),
            'restored' => $restored,
        ]);
    }

    public function deleteStoreTrashed(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'ids'   => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        $ids = $request->ids;
        $deleted = $this->trashService->forceDelete('branch', $ids);

        return response()->json([
            'message' => __('messages.force_delete_success', ['name' => 'Branch']),
            'deleted' => $deleted
        ]);
    }
}
