<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Notifications\NewFollowerNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    public function show(User $user): View
    {
        /** @var User $current */
        $current = auth()->user();

        $posts = Post::query()
            ->where('user_id', $user->id)
            ->with(['user', 'comments.user'])
            ->withCount('likes')
            ->withExists(['likes as liked' => fn ($q) => $q->where('user_id', $current->id)])
            ->latest()
            ->paginate(10);

        $followersCount = $user->followers()->count();
        $followingCount = $user->following()->count();
        $isFollowing = $current->follows($user);
        $isSelf = $current->id === $user->id;

        return view('profile.show', compact(
            'user',
            'posts',
            'followersCount',
            'followingCount',
            'isFollowing',
            'isSelf'
        ));
    }

    public function follow(User $user): RedirectResponse
    {
        /** @var User $current */
        $current = auth()->user();

        abort_if($current->id === $user->id, 403);

        if (! $current->follows($user)) {
            $current->following()->attach($user->id);

            if ($user->notify_on_follow) {
                $user->notify(new NewFollowerNotification($current));
            }
        }

        return redirect()->back()->with('status', 'followed');
    }

    public function unfollow(User $user): RedirectResponse
    {
        /** @var User $current */
        $current = auth()->user();

        abort_if($current->id === $user->id, 403);

        $current->following()->detach($user->id);

        return redirect()->back()->with('status', 'unfollowed');
    }
}
