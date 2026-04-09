<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Concerns\ResolvesOptionalSanctumUser;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    use ResolvesOptionalSanctumUser;

    public function index(Request $request): JsonResponse
    {
        $user = $this->optionalSanctumUser($request);

        $posts = Post::query()
            ->with('user')
            ->withCount('likes')
            ->when($user, function ($q) use ($user) {
                $q->withExists(['likes as liked' => fn ($qq) => $qq->where('user_id', $user->id)]);
            })
            ->latest()
            ->paginate(15);

        return response()->json($posts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:10000'],
            'image' => ['nullable', 'image', 'max:5120'],
        ], [], [
            'content' => 'текст',
            'image' => 'изображение',
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('posts', 'public');
        }

        $post = $request->user()->posts()->create([
            'content' => $validated['content'],
            'image' => $path,
        ]);

        $post->load('user');
        $post->loadCount('likes');

        return response()->json($post, 201);
    }
}
