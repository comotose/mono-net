<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $partnerIds = Message::query()
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
            })
            ->get()
            ->map(fn (Message $m) => $m->sender_id === $user->id ? $m->receiver_id : $m->sender_id)
            ->unique()
            ->values();

        $partners = User::query()
            ->whereIn('id', $partnerIds)
            ->orderBy('name')
            ->get();

        return view('messages.index', compact('partners'));
    }

    public function show(User $user): View
    {
        abort_if($user->id === auth()->id(), 404);

        $messages = Message::query()
            ->where(function ($q) use ($user) {
                $q->where(function ($q2) use ($user) {
                    $q2->where('sender_id', auth()->id())->where('receiver_id', $user->id);
                })->orWhere(function ($q2) use ($user) {
                    $q2->where('sender_id', $user->id)->where('receiver_id', auth()->id());
                });
            })
            ->with(['sender', 'receiver'])
            ->oldest()
            ->get();

        return view('messages.show', compact('user', 'messages'));
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        abort_if($user->id === auth()->id(), 403);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ], [], [
            'body' => 'сообщение',
        ]);

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $user->id,
            'body' => $validated['body'],
        ]);

        $message->load('sender');

        try {
            broadcast(new MessageSent($message));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('messages.show', $user)->with('status', 'message-sent');
    }
}
