<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function read(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()
            ->notifications()
            ->where('id', $notification)
            ->firstOrFail();

        $item->markAsRead();

        return redirect($item->data['url'] ?? route('notifications.index'));
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return redirect()->route('notifications.index')->with('status', 'notifications-read');
    }

    public function unread(Request $request): JsonResponse
    {
        $items = $request->user()
            ->unreadNotifications()
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'title' => $item->data['title'] ?? 'Уведомление',
                'text' => $item->data['text'] ?? '',
                'url' => $item->data['url'] ?? route('notifications.index'),
                'created_at' => $item->created_at->toIso8601String(),
            ])
            ->values();

        return response()->json([
            'notifications' => $items,
        ]);
    }
}
