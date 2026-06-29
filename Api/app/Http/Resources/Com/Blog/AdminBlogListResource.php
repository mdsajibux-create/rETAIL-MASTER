<?php

namespace App\Http\Resources\Com\Blog;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminBlogListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'blog_title' => $this->title,
            'category' => $this->category->name,
            'slug' => $this->slug,
            'image' => ImageModifier::generateImageUrl($this->image),
            'views' => $this->views,
            'visibility' => capitalize_first_letter($this->visibility),
            'status' => $this->status ? "Published" : "Draft",
            'schedule_date' => $this->schedule_date->format('Y-m-d'),
            'tags' => $this->tag_name,
            'author' => $this->author
        ];
    }

    public function with($request): array
    {
        return [
            'status' => true,
            'status_code' => 200,
            'message' => __('messages.data_found'),
        ];
    }
}
