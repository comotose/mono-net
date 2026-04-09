<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function show(User $user): JsonResponse
    {
        $user->loadCount(['followers', 'following', 'posts']);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'bio' => $user->bio,
            'avatar' => $user->avatarUrl(),
            'followers_count' => $user->followers_count,
            'following_count' => $user->following_count,
            'posts_count' => $user->posts_count,
            'created_at' => $user->created_at,
        ]);
    }
}
