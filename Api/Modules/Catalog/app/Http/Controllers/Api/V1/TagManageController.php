<?php

namespace Modules\Catalog\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\TagRequest;
use App\Interfaces\TagInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TagManageController extends Controller
{
    public function __construct(protected TagInterface $tagRepo)
    {
    }

    public function listTags(Request $request)
    {
        return $this->tagRepo->getPaginatedTag(
            $request->limit ?? 10,
            $request->page ?? 1,
            $request->language ?? DEFAULT_LANGUAGE,
            $request->search ?? "",
            $request->sortField ?? 'id',
            $request->sort ?? 'asc',
            []
        );
    }

    public function createTag(TagRequest $request): JsonResponse
    {
        $created_by = Auth::user()->id;
        $request['created_by'] = $created_by;

        $tag = $this->tagRepo->store($request->all());

        createOrUpdateTranslation($request, $tag, 'Modules\Catalog\app\Models\Tag', $this->tagRepo->translationKeys());

        if ($tag) {
            return $this->success(translate('messages.save_success', ['name' => 'Tag']));
        } else {
            return $this->failed(translate('messages.save_failed', ['name' => 'Tag']));
        }
    }

    public function getTagById(Request $request)
    {
        return $this->tagRepo->getTagById($request->id);
    }

    public function updateTag(TagRequest $request)
    {
        $tag = $this->tagRepo->update($request->all());
        createOrUpdateTranslation($request, $tag, 'Modules\Catalog\app\Models\Tag', $this->tagRepo->translationKeys());
        if ($tag) {
            return $this->success(translate('messages.update_success', ['name' => 'Tag']));
        } else {
            return $this->failed(translate('messages.update_failed', ['name' => 'Tag']));
        }
    }

    public function deleteTags(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'nullable|exists:tags,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        foreach ($request->ids as $id) {
            $this->tagRepo->delete($id);
        }
        return $this->success(translate('messages.delete_success'));
    }
}
