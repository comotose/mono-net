<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Post;
use App\Models\Reaction;
use App\Notifications\PostLikedNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    public function storeForPost(Request $request, Post $post): JsonResponse
    {
        $this->syncReaction($request, $post);
        $post->load(['reactions', 'user']);

        return response()->json([
            'html' => view('reactions._picker', ['subject' => $post])->render(),
        ]);
    }

    public function storeForMessage(Request $request, Message $message): JsonResponse
    {
        abort_unless(
            in_array($request->user()->id, [$message->sender_id, $message->receiver_id], true),
            403
        );

        $this->syncReaction($request, $message);
        $message->load('reactions');

        return response()->json([
            'html' => view('reactions._picker', ['subject' => $message])->render(),
        ]);
    }

    private function syncReaction(Request $request, Model $subject): void
    {
        $validated = $request->validate([
            'kind' => ['required', 'in:'.implode(',', Reaction::kinds())],
        ], [], [
            'kind' => 'реакция',
        ]);

        $reaction = $subject->reactions()
            ->where('user_id', $request->user()->id)
            ->first();

        if ($reaction && $reaction->kind === $validated['kind']) {
            $reaction->delete();
            return;
        }

        $wasMissing = $reaction === null;

        $subject->reactions()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['kind' => $validated['kind']]
        );

        if ($wasMissing && $subject instanceof Post && $subject->user_id !== $request->user()->id && $subject->user->notify_on_like) {
            $subject->user->notify(new PostLikedNotification($request->user(), $subject));
        }
    }
}
