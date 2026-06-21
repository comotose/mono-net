<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Notifications\PostCommentedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ], [], [
            'body' => 'комментарий',
            'parent_id' => 'родительский комментарий',
        ]);

        $parent = null;
        if (! empty($validated['parent_id'])) {
            $parent = Comment::query()->where('post_id', $post->id)->findOrFail($validated['parent_id']);
        }

        $comment = $request->user()->comments()->create([
            'post_id' => $post->id,
            'parent_id' => $parent?->id,
            'body' => $validated['body'],
        ]);

        if ($post->user_id !== $request->user()->id && $post->user->notify_on_comment) {
            $post->user->notify(new PostCommentedNotification($request->user(), $post, $comment));
        }

        $comment->load(['user', 'parent.user', 'replies']);
        $depth = $this->commentDepth($comment);

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('posts._comment', ['comment' => $comment, 'post' => $post, 'depth' => $depth])->render(),
                'post_id' => $post->id,
                'parent_id' => $comment->parent_id,
            ], 201);
        }

        return redirect()->back()->with('status', 'comment-added');
    }

    public function destroy(Request $request, Comment $comment): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        abort_unless(
            $comment->user_id === $user->id
            || $comment->post->user_id === $user->id
            || $user->hasRole('admin', 'moderator'),
            403
        );

        $comment->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $comment->id,
                'post_id' => $comment->post_id,
            ]);
        }

        return redirect()->back()->with('status', 'comment-deleted');
    }

    private function commentDepth(Comment $comment): int
    {
        $depth = 0;
        $parent = $comment->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }
}
