<?php

namespace App\Repositories;

use App\Http\Resources\DepartmentDetailsResource;
use App\Interfaces\AllDepartmentManageInterface;
use App\Models\Department;
use App\Models\Translation;

class AllDepartmentManageRepository implements AllDepartmentManageInterface
{
    public function __construct(protected Department $department, protected Translation $translation)
    {
    }

    public function translationKeys(): mixed
    {
        return $this->department->translationKeys;
    }

    public function getPaginatedDepartments(int|string $perPage, string $search, string $sortField, string $sort)
    {
        $query = Department::query();

        // Apply search filter if a search parameter exists
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Apply sorting
        $query->orderBy($sortField, $sort);

        // Paginate the results
        return $query->latest()->paginate($perPage);
    }

    public function store(array $data)
    {
        try {
            $department = $this->department->create($data);
            return $department->id;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getDepartmentById(int|string $id)
    {
        try {
            $department = $this->department->findorfail($id);
            return response()->json(new DepartmentDetailsResource($department));
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(array $data)
    {
        try {
            $department = $this->department->findorfail($data['id']);
            if ($department) {
                $department->update($data);
                return $department->id;
            } else {
                return false;
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function delete(int|string $id)
    {
        try {
            $department = $this->department->findOrFail($id);
            $department->delete();
            return true;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
