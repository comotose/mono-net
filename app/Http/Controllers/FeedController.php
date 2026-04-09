<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\View\View;

class FeedController extends Controller
{
    public function index(): View
    {
        $posts = Post::query()
            ->with(['user', 'comments.user'])
            ->withCount('likes')
            ->withExists(['likes as liked' => fn ($q) => $q->where('user_id', auth()->id())])
            ->latest()
            ->paginate(15);

        return view('feed.index', compact('posts'));
    }
}
