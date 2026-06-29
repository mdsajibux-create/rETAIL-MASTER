<?php

namespace Modules\Blog\app\Http\Controllers\Api;


use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Com\Blog\BlogCategoryPublicResource;
use App\Http\Resources\Com\Blog\BlogCommentResource;
use App\Http\Resources\Com\Blog\BlogDetailsPublicResource;
use App\Http\Resources\Com\Blog\BlogPublicResource;
use App\Http\Resources\Com\PaginationResource;
use Illuminate\Http\Request;
use Modules\Blog\app\Models\Blog;
use Modules\Blog\app\Models\BlogCategory;
use Modules\Blog\app\Models\BlogComment;
use Modules\Blog\app\Models\BlogView;
use Modules\SystemCore\app\Models\SettingOption;

class FrontendBlogController extends Controller
{
    public function blogs(Request $request)
    {
        $blogsQuery = Blog::with(['category', 'related_translations'])
            ->where(function ($query) {
                $query->where('status', 1)
                    ->where(function ($q) {
                        $q->whereDate('schedule_date', '<=', now())
                            ->orWhereNull('schedule_date');
                    });
            });

        // Check for "most_viewed" filter
        if ($request->has('most_viewed') && $request->most_viewed) {
            $blogsQuery->orderBy('views', 'desc');  // Assuming you have a 'views' column
        }

        if ($request->has('search') && $request->search) {
            $blogsQuery->where(function ($query) use ($request) {
                $query->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('category_id') && $request->category_id) {
            $blogsQuery->where('category_id', $request->category_id);  // Assuming you have a 'views' column
        }

        // Check for sort filter (sort by created_at only)
        if ($request->has('sort') && $request->sort) {
            // Ensure the sort direction is either 'asc' or 'desc'
            $sortDirection = strtolower($request->sort) == 'asc' ? 'asc' : 'desc'; // Default to 'desc' if not 'asc'
            $blogsQuery->orderBy('created_at', $sortDirection);  // Sort only by 'created_at'
        }

        // Pagination
        $perPage = $request->has('per_page') ? $request->per_page : 10;  // Default to 10 if not provided
        $blogs = $blogsQuery->paginate($perPage);

        return response()->json([
            'data' => BlogPublicResource::collection($blogs),
            'meta' => new PaginationResource($blogs)
        ], 200);
    }

    public function blogDetails(Request $request)
    {
        $blog = Blog::with('category')
            ->where('slug', $request->slug)
            ->first();
        if (!$blog) {
            return response()->json([
                'message' => __('messages.data_not_found')
            ], 404);
        }
        // Track unique user views
        if (auth('api_customer')->check()) { // If user is logged in
            $user = auth('api_customer')->user();
            // Check if user has already viewed this blog
            $viewExists = BlogView::where('blog_id', $blog->id)
                ->where('user_id', $user->id)
                ->exists();
            if (!$viewExists) {
                // Increment view count for this blog
                $blog->increment('views');
                // Store the view record in the `blog_views` table
                BlogView::create([
                    'blog_id' => $blog->id,
                    'user_id' => $user->id,
                ]);
            }
        } else {
            // For guests, you can track by IP address
            $ipAddress = $request->ip();
            $viewExists = BlogView::where('blog_id', $blog->id)
                ->where('ip_address', $ipAddress)
                ->exists();
            if (!$viewExists) {
                // Increment view count for this blog
                $blog->increment('views');
                // Store the view record with the IP address
                BlogView::create([
                    'blog_id' => $blog->id,
                    'ip_address' => $ipAddress,
                ]);
            }
        }


        // Blog categories
        $all_blog_categories = BlogCategory::where('status', 1)
            ->limit(15)
            ->latest()
            ->get();

        // popular posts
        $popular_posts = Blog::with('category')
            ->orderBy('views', 'desc')
            ->where('status', 1)
            ->whereDate('schedule_date', '<=', now())// Only blogs with a schedule date <= today's date
            ->orWhereNull('schedule_date')
            ->limit(5)
            ->get();

        // related posts
        $related_posts = $blog->relatedBlogs()->get();

        // If no related posts found, fetch fallback blogs
        if ($related_posts->isEmpty()) {
            $related_posts = Blog::where('status', 1)
                ->whereDate('schedule_date', '<=', now())
                ->orWhereNull('schedule_date')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }
        if ($related_posts->isEmpty()) {
            // Try fetching most viewed posts if no related ones are found
            $related_posts = Blog::where('status', 1)
                ->orWhereNull('schedule_date')
                ->orderBy('created_at', 'desc')
                ->orderBy('views', 'desc')
                ->limit(5)
                ->get();
        }

        // If still empty, get random blogs
        if ($related_posts->isEmpty()) {
            $related_posts = Blog::where('status', 1)
                ->orWhereNull('schedule_date')
                ->orderBy('created_at', 'desc')
                ->inRandomOrder()
                ->limit(5)
                ->get();
        }
        $blog_comments = BlogComment::where('blog_id', $blog->id)->with(['user', 'blogCommentReactions'])->orderByLikeDislikeRatio()->get();
        return response()->json([
            'blog_details' => new BlogDetailsPublicResource($blog),
            'all_blog_categories' => BlogCategoryPublicResource::collection($all_blog_categories),
            'popular_posts' => BlogPublicResource::collection($popular_posts),
            'related_posts' => BlogPublicResource::collection($related_posts),
            'blog_comments' => BlogCommentResource::collection($blog_comments),
            'total_comments' => $blog_comments->count()
        ], 200);
    }

    public function BlogPageSettings(Request $request)
    {
        $language = $request->input('language', 'en'); // Default language is 'en'

        $ComOptionGet = SettingOption::with('translations')->whereIn('option_name', [
            'com_blog_details_popular_title',
            'com_blog_details_related_title',
        ])->get();

        // Default settings
        $page_settings = [
            'com_blog_details_popular_title' => com_option_get('com_blog_details_popular_title') ?? '',
            'com_blog_details_related_title' => com_option_get('com_blog_details_related_title') ?? '',
        ];

        // Replace with translation values based on requested language
        foreach ($ComOptionGet as $settingOption) {
            $translation = $settingOption->translations->where('language', $language)->first();

            if ($translation) {
                $page_settings[$settingOption->option_name] = trim($translation->value, '"');
            }
        }

        return response()->json(['data' => $page_settings]);
    }
}
