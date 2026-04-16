<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Notifications\PostCommentedNotification;
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

        $comment = $request->user()->comments()->create([
            'post_id' => $post->id,
            'body' => $validated['body'],
        ]);

        if ($post->user_id !== $request->user()->id && $post->user->notify_on_comment) {
            $post->user->notify(new PostCommentedNotification($request->user(), $post, $comment));
        }

        return redirect()->back()->with('status', 'comment-added');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $user = request()->user();
        abort_unless(
            $comment->user_id === $user->id
            || $comment->post->user_id === $user->id
            || $user->hasRole('admin', 'moderator'),
            403
        );

        $comment->delete();

        return redirect()->back()->with('status', 'comment-deleted');
    }
}
