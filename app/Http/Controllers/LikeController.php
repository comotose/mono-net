<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use App\Notifications\PostLikedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function toggle(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();

        $like = Like::query()
            ->where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            Like::create([
                'user_id' => $user->id,
                'post_id' => $post->id,
            ]);
            $liked = true;

            if ($post->user_id !== $user->id && $post->user->notify_on_like) {
                $post->user->notify(new PostLikedNotification($user, $post));
            }
        }

        $count = $post->likes()->count();

        return response()->json([
            'liked' => $liked,
            'count' => $count,
        ]);
    }
}
