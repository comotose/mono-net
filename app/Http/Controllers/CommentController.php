<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ], [], [
            'body' => 'комментарий',
        ]);

        $request->user()->comments()->create([
            'post_id' => $post->id,
            'body' => $validated['body'],
        ]);

        return redirect()->back()->with('status', 'comment-added');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $user = request()->user();
        abort_unless(
            $comment->user_id === $user->id || $comment->post->user_id === $user->id,
            403
        );

        $comment->delete();

        return redirect()->back()->with('status', 'comment-deleted');
    }
}
