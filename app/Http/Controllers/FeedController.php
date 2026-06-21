<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\View\View;

class FeedController extends Controller
{
    public function index(): View
    {
        $posts = Post::query()
            ->with(['user', 'comments', 'comments.replies', 'reactions'])
            ->latest()
            ->paginate(15);

        return view('feed.index', compact('posts'));
    }
}
