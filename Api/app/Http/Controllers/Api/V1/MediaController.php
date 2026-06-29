<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\ImageModifier;
use App\Http\Resources\Com\PaginationResource;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\SystemCore\app\Models\Media;

class MediaController extends Controller
{

    protected $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function mediaUpload(Request $request)
    {
        if (config('demoMode.check')) {
            return response()->json([
                'status' => 'error',
                'message' => 'This action is disabled in demo mode.',
            ], 403);
        }

        if (empty($request->all())) {
            return response()->json([
                'error' => 'No data provided in the request.',
            ], 400);
        }

        $rules = [
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,webp|max:10240', // max size 10MB
        ];
        $validator = Validator::make($request->all(), $rules);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 400);
        }

        $media = $this->mediaService->insert_media_image($request);

        return response()->json([
            'message' => 'Media uploaded successfully.',
            'image_id' => $media->id ?? null,
            'image_url' => com_option_get_id_wise_url($media->id) ?? null,
        ], 201);
    }

    public function load_more(Request $request)
    {
        $all_images = $this->mediaService->load_more_images($request);
        return response()->json([
            'images' => $all_images,
        ]);
    }

    public function alt_change(Request $request)
    {
        $request->validate([
            'image_id' => 'required|integer|exists:media,id',
            'alt' => 'nullable|string|max:255',
        ]);

        $response = $this->mediaService->image_alt_change($request);
        return response()->json([
            'message' => $response['msg'],
            'success' => $response['success'],
        ], 200);
    }

    public function delete_media(Request $request)
    {

        if (config('demoMode.check')) {
            return response()->json([
                'status' => 'error',
                'message' => 'This action is disabled in demo mode.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'image_id' => 'required|integer|exists:media,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $response = $this->mediaService->delete_media_image($request);
        return response()->json([
            'message' => $response['msg'],
            'success' => $response['success'],
        ], $response['success'] ? 200 : 500);
    }

    public function allMediaManage(Request $request)
    {
        $query = Media::query();
        // Filter by media name
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $all_media = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $all_media->map(function ($item) {
                return [
                    'id' => $item->id,
                    'img_url' => ImageModifier::generateImageUrl($item->id),
                    'name' => $item->name,
                    'format' => $item->format,
                    'size' => $item->file_size,
                    'dimensions' => $item->dimensions,
                    'path' => $item->path,
                ];
            }),
            'meta' => new PaginationResource($all_media)
        ]);
    }

    public function mediaFileDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:media,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->mediaService->bulkDeleteMediaImages($request->ids);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'deleted_count' => $result['deleted'],
            'failed_count' => $result['failed'],
        ], $result['success'] ? 200 : 500);
    }

}

