<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'post_id' => ['required', 'exists:posts,id'],
        ], [], [
            'post_id' => 'публикация',
        ]);

        $post = Post::findOrFail($validated['post_id']);
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
        }

        return response()->json([
            'liked' => $liked,
            'count' => Like::where('post_id', $post->id)->count(),
            'post_id' => $post->id,
        ]);
    }
}
