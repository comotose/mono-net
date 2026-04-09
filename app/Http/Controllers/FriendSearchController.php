<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class FriendSearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $users = new Collection;
        $followingIds = new Collection;

        if (mb_strlen($q) >= 2) {
            $escaped = addcslashes($q, '%_\\');

            $users = User::query()
                ->where('id', '!=', $request->user()->id)
                ->where(function ($query) use ($escaped) {
                    $query->where('name', 'like', '%'.$escaped.'%')
                        ->orWhere('email', 'like', '%'.$escaped.'%')
                        ->orWhere('bio', 'like', '%'.$escaped.'%');
                })
                ->orderBy('name')
                ->limit(40)
                ->get();

            if ($users->isNotEmpty()) {
                $followingIds = $request->user()->following()
                    ->whereIn('users.id', $users->pluck('id'))
                    ->pluck('users.id');
            }
        }

        return view('search.friends', [
            'q' => $q,
            'users' => $users,
            'followingIds' => $followingIds,
        ]);
    }
}
