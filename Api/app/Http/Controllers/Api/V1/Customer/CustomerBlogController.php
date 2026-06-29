<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\CommentRequest;
use App\Interfaces\BlogManageInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Blog\app\Models\BlogComment;
use Modules\Blog\app\Models\BlogCommentReaction;

class CustomerBlogController extends Controller
{
    public function __construct(protected BlogManageInterface $blogRepo)
    {
    }

    public function addComment(CommentRequest $request)
    {
        try {
            if (!auth()->guard('api_customer')->check()) {
                return unauthorized_response();
            } else {
                $userId = auth('api_customer')->user()->id;
                $request['user_id'] = $userId;
                BlogComment::create($request->all());
                return response()->json([
                    'status' => true,
                    'status_code' => 201,
                    'message' => __('messages.save_success', ['name' => 'Comment']),
                ]);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function addReaction(Request $request)
    {
        if (!auth()->guard('api_customer')->check()) {
            return unauthorized_response();
        }

        $validator = Validator::make($request->all(), [
            'blog_comment_id' => 'required|exists:blog_comments,id',
            'reaction_type' => 'required|in:like,dislike',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'status_code' => 422,
                'errors' => $validator->errors()
            ], 422);
        }

        $success = $this->handleReaction($request->blog_comment_id, $request->reaction_type);

        return response()->json([
            'status' => $success,
            'status_code' => $success ? 200 : 400,
            'message' => __($success ? 'messages.update_success' : 'messages.update_failed', ['name' => 'Reaction'])
        ]);
    }

    private function handleReaction($blogCommentId, $reactionType)
    {
        $userId = auth('api_customer')->id();

        // Fetch blog comment and ensure it exists
        $blogComment = BlogComment::where('id', $blogCommentId)->firstOrFail();

        return DB::transaction(function () use ($blogComment, $userId, $reactionType) {
            // Fetch existing reaction
            $existingReaction = BlogCommentReaction::where('blog_comment_id', $blogComment->id)
                ->where('user_id', $userId)
                ->first();

            if ($existingReaction) {
                if ($existingReaction->reaction_type === $reactionType) {
                    // Remove reaction
                    $existingReaction->delete();
                    $blogComment->decrement("{$reactionType}_count");
                } else {
                    // Switch reaction type
                    $oldReactionType = $existingReaction->reaction_type;
                    $existingReaction->update(['reaction_type' => $reactionType]);

                    // Update counts
                    $blogComment->increment("{$reactionType}_count");
                    $blogComment->decrement("{$oldReactionType}_count");
                }
            } else {
                // New reaction
                BlogCommentReaction::create([
                    'blog_comment_id' => $blogComment->id,
                    'user_id' => $userId,
                    'reaction_type' => $reactionType,
                ]);

                $blogComment->increment("{$reactionType}_count");
            }

            return true;
        });
    }
}
