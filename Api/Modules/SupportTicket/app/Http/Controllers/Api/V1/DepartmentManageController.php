<?php

namespace Modules\SupportTicket\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Com\PaginationResource;
use App\Http\Resources\DepartmentResource;
use App\Interfaces\AllDepartmentManageInterface;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;

class DepartmentManageController extends Controller
{
    public function __construct(protected AllDepartmentManageInterface $departmentRepo)
    {

    }

    public function listDepartments(Request $request)
    {
        $departments = $this->departmentRepo->getPaginatedDepartments(
            $request->per_page ?? 10,
            $request->search ?? '',
            $request->sort_by ?? 'id',
            $request->sort ?? 'desc',
        );

        return response()->json([
            'data' => DepartmentResource::collection($departments),
            'meta' => new PaginationResource($departments),
        ]);
    }

    public function createDepartment(Request $request): JsonResponse
    {
        $rules = [
            'name' => 'required|string|max:255',
            'status' => 'required|integer',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $department = $this->departmentRepo->store($request->all());
        createOrUpdateTranslation($request, $department, Department::class, $this->departmentRepo->translationKeys());
        if ($department) {
            return $this->success(translate('messages.save_success', ['name' => 'Department']));
        } else {
            return $this->failed(translate('messages.save_failed', ['name' => 'Department']));
        }
    }

    public function updateDepartment(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'status' => 'required|integer',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $department = $this->departmentRepo->update($request->all());

        createOrUpdateTranslation($request, $department, Department::class, $this->departmentRepo->translationKeys());

        if ($department) {
            return $this->success(translate('messages.update_success', ['name' => 'Department']));
        } else {
            return $this->failed(translate('messages.update_failed', ['name' => 'Department']));
        }
    }

    public function getDepartmentById(Request $request)
    {
        return $this->departmentRepo->getDepartmentById($request->id);
    }

    public function deleteDepartment($id)
    {
        $this->departmentRepo->delete($id);
        return $this->success(translate('messages.delete_success', ['name' => 'Department']));
    }

}
