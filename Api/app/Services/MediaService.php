<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Modules\Branch\app\Models\Branch;
use Modules\SystemCore\app\Models\Media;


class MediaService
{
    /**
     * Upload and process a media file.
     *
     * @param UploadedFile $file
     * @return Media
     */

    public function insert_media_image($request, $type = 'admin', $file_field_name = 'file', $folder_type = 'default')
    {
        if ($request->hasFile($file_field_name)) {

            $image = $request->$file_field_name;


            // Define the base path and subfolder path
            $base_path = 'uploads/media-uploader';
            $folder_path = "{$base_path}/{$folder_type}";

            if ($type == 'deliveryman') {
                $base_path = 'uploads/deliveryman';
                $folder_path = "{$base_path}/{$folder_type}";
            }

            // Create the directory if it doesn't exist
            if (!File::exists(storage_path("app/public/{$folder_path}"))) {
                File::makeDirectory(storage_path("app/public/{$folder_path}"), 0755, true);
            }

            // Get image details
            $image_dimension = getimagesize($image);
            $image_width = $image_dimension[0];
            $image_height = $image_dimension[1];
            $image_dimension_for_db = $image_width . ' x ' . $image_height . ' pixels';
            $image_size_for_db = $image->getSize();
            $image_extenstion = $image->getClientOriginalExtension();
            $image_name_with_ext = $image->getClientOriginalName();
            $image_name = strtolower(Str::slug(pathinfo($image_name_with_ext, PATHINFO_FILENAME)));

            $image_db = $image_name . time() . '.' . $image_extenstion;

            // Resize and save images
            $resize_full_image = Image::make($image)->resize($image_width, $image_height, function ($constraint) {
                $constraint->aspectRatio();
            });


            // Save images to storage
            $resize_full_image->save(storage_path("app/public/{$folder_path}/{$image_db}"));


            $user_id = auth('sanctum')->id();
            $user_type = get_class(auth('sanctum')->user());
            $usage_type = $request->usage_type;

            // Save to the database and return the Media instance
            return Media::create([
                'name' => $image_name_with_ext,
                'format' => strtolower($image_extenstion),
                'file_size' => formatBytes($image_size_for_db),
                'path' => "{$folder_path}/{$image_db}",
                'dimensions' => $image_dimension_for_db,
                'user_id' => $user_id,
                'user_type' => $user_type,
                'usage_type' => $usage_type ?? null,
            ]);
        }

        return null;
    }

    public function load_more_images($request)
    {
        // user type
        $user = auth('sanctum')->user();
        $user_id = $user->id;
        $user_type = '';

        // check only store
        if ( $user->activity_scope === 'system_level' || $user->activity_scope === 'delivery_level' || ($user->activity_scope === 'branch_level' && empty($request->branch_id))
        ) {
            $user_type = User::class;
        } else {
            $user_type = 'App\Models\Customer';
        }


        $image_query = Media::query();
        $image_query->where('user_id', $user_id)
            ->where('user_type', $user_type);

        $offset = $request->get('offset') ?? 0;

        $all_images = $image_query->orderBy('created_at', 'DESC')
            ->skip($offset)
            ->latest()
            ->take(20)
            ->get();

        $all_image_files = [];
        foreach ($all_images as $image) {

            // Generate the public URL directly
            $image_url = asset("storage/{$image->path}");

            // Check if the grid version exists (without file_exists, use URL generation)
            $grid_image_url = asset("storage/uploads/media-uploader/default/" . basename($image->path));

            // If the grid version URL is valid, use that
            if ($grid_image_url) {
                $image_url = $grid_image_url;
            }

            $all_image_files[] = [
                'image_id' => $image->id,
                'name' => $image->name,
                'dimensions' => $image->dimensions,
                'alt' => $image->alt_text,
                'size' => $image->file_size,
                'path' => $image->path,
                'img_url' => $image_url,
                'upload_at' => date_format($image->created_at, 'd M y')
            ];
        }

        return $all_image_files;
    }

    public function image_alt_change($request)
    {
        $update = Media::where('id', $request->image_id)
            ->where('user_id', auth('sanctum')->id())
            ->update([
                'alt_text' => $request->alt,
            ]);
        return [
            'msg' => $update ? 'Alt text updated successfully' : 'Failed to update alt text',
            'success' => (bool)$update,
        ];
    }


    public function delete_media_image($request)
    {

        $get_image_details = Media::find($request->image_id);

        // Check if the image exists
        if (!$get_image_details) {
            return [
                'msg' => 'Image not found',
                'success' => false,
            ];
        }

        $base_path = storage_path('app/public/');
        $image_path = $base_path . $get_image_details->path;

        // Delete the main image if it exists
        if (file_exists($image_path)) {
            unlink($image_path);
        }

        // Delete the image record from the database
        $image_find = Media::where('user_id', auth('sanctum')->id())
            ->where('id', $request->image_id)
            ->delete();

        if ($image_find) {
            return [
                'msg' => 'Image and its variants deleted successfully',
                'success' => true,
            ];
        } else {
            return [
                'msg' => 'Failed to delete the image',
                'success' => false,
            ];
        }
    }


    public function bulkDeleteMediaImages(array $ids): array
    {
        $base_path = storage_path('app/public/');

        $deleted = 0;
        $failed = 0;

        foreach ($ids as $id) {
            $media = Media::find((int)$id);

            if (!$media) {
                $failed++;
                continue;
            }

            $folder_path = $media->path;

            // Delete image
            $variant_path = $base_path . $folder_path;
            if (file_exists($variant_path)) {
                @unlink($variant_path);
            }

            // Delete DB record
            if ($media->delete()) {
                $deleted++;
            } else {
                $failed++;
            }
        }

        return [
            'success' => $deleted > 0,
            'message' => $deleted > 0
                ? "Deleted $deleted media file(s), $failed failed."
                : 'No media files were deleted.',
            'deleted' => $deleted,
            'failed' => $failed,
        ];
    }


}
