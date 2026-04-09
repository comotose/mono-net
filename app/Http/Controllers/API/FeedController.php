<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Concerns\ResolvesOptionalSanctumUser;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends Controller
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
}
