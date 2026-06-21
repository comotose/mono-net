<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Notifications\NewFollowerNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            ->with(['user', 'comments', 'comments.replies', 'reactions'])
            ->latest()
            ->paginate(10);

        $followersCount = $user->followers()->count();
        $followingCount = $user->following()->count();
        $followers = $user->followers()->orderBy('name')->get();
        $following = $user->following()->orderBy('name')->get();
        $isFollowing = $current->follows($user);
        $isSelf = $current->id === $user->id;

        return view('profile.show', compact(
            'user',
            'posts',
            'followersCount',
            'followingCount',
            'followers',
            'following',
            'isFollowing',
            'isSelf'
        ));
    }

    public function follow(Request $request, User $user): RedirectResponse|JsonResponse
    {
        /** @var User $current */
        $current = $request->user();

        abort_if($current->id === $user->id, 403);

        if (! $current->follows($user)) {
            $current->following()->attach($user->id);

            if ($user->notify_on_follow) {
                $user->notify(new NewFollowerNotification($current));
            }
        }

        if ($request->expectsJson()) {
            return $this->followResponse($request, $user, true);
        }

        return redirect()->back()->with('status', 'followed');
    }

    public function unfollow(Request $request, User $user): RedirectResponse|JsonResponse
    {
        /** @var User $current */
        $current = $request->user();

        abort_if($current->id === $user->id, 403);

        $current->following()->detach($user->id);

        if ($request->expectsJson()) {
            return $this->followResponse($request, $user, false);
        }

        return redirect()->back()->with('status', 'unfollowed');
    }

    public function updateRole(Request $request, User $user): RedirectResponse|JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'role' => ['required', 'in:'.implode(',', array_keys(User::availableRoles()))],
        ], [], [
            'role' => 'роль',
        ]);

        $user->update([
            'role' => $validated['role'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'role_label' => $user->roleLabel(),
                'badge_html' => view('users._role_badge', ['user' => $user])->render(),
                'manager_html' => view('users._role_manager', ['user' => $user])->render(),
            ]);
        }

        return redirect()->back()->with('status', 'role-updated');
    }

    private function followResponse(Request $request, User $user, bool $isFollowing): JsonResponse
    {
        $current = $request->user();

        return response()->json([
            'is_following' => $isFollowing,
            'followers_count' => $user->followers()->count(),
            'following_count' => $user->following()->count(),
            'button_html' => view('users._follow_button', [
                'user' => $user,
                'isFollowing' => $isFollowing,
                'isSelf' => $current->id === $user->id,
            ])->render(),
        ]);
    }
}
