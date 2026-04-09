<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function store(Request $request): RedirectResponse
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

        $request->user()->posts()->create([
            'content' => $validated['content'],
            'image' => $path,
        ]);

        return redirect()->route('feed.index')->with('status', 'post-created');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorizePost($post);

        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return redirect()->route('feed.index')->with('status', 'post-deleted');
    }

    private function authorizePost(Post $post): void
    {
        abort_unless($post->user_id === auth()->id(), 403);
    }
}
