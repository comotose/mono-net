<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:10000'],
            'images' => ['nullable', 'array', 'max:9'],
            'images.*' => ['image', 'max:5120'],
        ], [], [
            'content' => 'текст',
            'images' => 'изображения',
            'images.*' => 'изображение',
        ]);

        $paths = [];
        foreach ($request->file('images', []) as $image) {
            $paths[] = $image->store('posts', 'public');
        }

        $post = $request->user()->posts()->create([
            'content' => $validated['content'],
            'image' => $paths[0] ?? null,
            'images' => $paths,
        ]);

        $post->load(['user', 'comments', 'comments.replies', 'reactions']);

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('posts._card', ['post' => $post])->render(),
            ], 201);
        }

        return redirect()->route('feed.index')->with('status', 'post-created');
    }

    public function destroy(Request $request, Post $post): RedirectResponse|JsonResponse
    {
        $this->authorizePost($post);

        foreach ($post->imagePaths() as $path) {
            Storage::disk('public')->delete($path);
        }

        $post->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $post->id,
            ]);
        }

        return redirect()->route('feed.index')->with('status', 'post-deleted');
    }

    private function authorizePost(Post $post): void
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        abort_unless(
            $post->user_id === $user->id || $user->hasRole('admin', 'moderator'),
            403
        );
    }
}
