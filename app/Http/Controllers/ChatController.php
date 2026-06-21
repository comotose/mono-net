<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $onlyUnread = $request->boolean('unread');

        $conversationQuery = Message::query()
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
            });

        if ($onlyUnread) {
            $conversationQuery
                ->where('receiver_id', $user->id)
                ->whereNull('read_at');
        }

        $partnerIds = $conversationQuery
            ->get(['sender_id', 'receiver_id'])
            ->map(fn (Message $m) => $m->sender_id === $user->id ? $m->receiver_id : $m->sender_id)
            ->unique()
            ->values();

        $partners = User::query()
            ->whereIn('id', $partnerIds)
            ->withCount([
                'sentMessages as unread_messages_count' => fn ($q) => $q
                    ->where('receiver_id', $user->id)
                    ->whereNull('read_at'),
            ])
            ->orderByDesc('unread_messages_count')
            ->orderBy('name')
            ->get();

        $unreadDialogsCount = Message::query()
            ->where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->distinct('sender_id')
            ->count('sender_id');

        return view('messages.index', compact('partners', 'onlyUnread', 'unreadDialogsCount'));
    }

    public function show(User $user): View
    {
        abort_if($user->id === auth()->id(), 404);

        Message::query()
            ->where('sender_id', $user->id)
            ->where('receiver_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = Message::query()
            ->where(function ($q) use ($user) {
                $q->where(function ($q2) use ($user) {
                    $q2->where('sender_id', auth()->id())->where('receiver_id', $user->id);
                })->orWhere(function ($q2) use ($user) {
                    $q2->where('sender_id', $user->id)->where('receiver_id', auth()->id());
                });
            })
            ->with(['sender', 'receiver', 'reactions'])
            ->oldest()
            ->get();

        return view('messages.show', compact('user', 'messages'));
    }

    public function store(Request $request, User $user): RedirectResponse|JsonResponse
    {
        abort_if($user->id === auth()->id(), 403);

        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:5000', 'required_without_all:attachment,voice'],
            'attachment' => ['nullable', 'file', 'max:10240', 'required_without_all:body,voice'],
            'voice' => ['nullable', 'file', 'max:10240', 'required_without_all:body,attachment'],
        ], [
            'body.required_without_all' => 'Введите сообщение или прикрепите файл/голосовое.',
            'attachment.required_without_all' => 'Введите сообщение или прикрепите файл/голосовое.',
            'voice.required_without_all' => 'Введите сообщение или прикрепите файл/голосовое.',
            'body.max' => 'Сообщение не должно быть длиннее :max символов.',
            'attachment.max' => 'Размер файла не должен превышать 10 МБ.',
            'voice.max' => 'Размер голосового сообщения не должен превышать 10 МБ.',
            'attachment.file' => 'В поле файла должно быть передано корректное вложение.',
            'voice.file' => 'Голосовое сообщение должно быть корректным файлом.',
        ], [
            'body' => 'сообщение',
            'attachment' => 'файл',
            'voice' => 'голосовое сообщение',
        ]);

        $type = 'text';
        $attachmentPath = null;
        $attachmentOriginalName = null;
        $attachmentMime = null;
        $attachmentSize = null;

        if ($request->hasFile('voice')) {
            $voice = $request->file('voice');
            $type = 'voice';
            $attachmentPath = $voice->store('messages/voices', 'public');
            $attachmentOriginalName = $voice->getClientOriginalName() ?: 'voice-message.webm';
            $attachmentMime = $voice->getClientMimeType();
            $attachmentSize = $voice->getSize();
        } elseif ($request->hasFile('attachment')) {
            $attachment = $request->file('attachment');
            $attachmentMime = $attachment->getClientMimeType();
            $type = str_starts_with((string) $attachmentMime, 'image/') ? 'image' : 'file';
            $attachmentPath = $attachment->store('messages/attachments', 'public');
            $attachmentOriginalName = $attachment->getClientOriginalName();
            $attachmentSize = $attachment->getSize();
        }

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $user->id,
            'body' => isset($validated['body']) ? trim($validated['body']) : null,
            'type' => $type,
            'attachment_path' => $attachmentPath,
            'attachment_original_name' => $attachmentOriginalName,
            'attachment_mime' => $attachmentMime,
            'attachment_size' => $attachmentSize,
        ]);

        $message->load(['sender', 'receiver', 'reactions']);

        if ($user->notify_on_message) {
            $user->notify(new NewMessageNotification($message));
        }

        try {
            broadcast(new MessageSent($message));
        } catch (\Throwable $e) {
            report($e);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('messages._row', ['message' => $message])->render(),
                'message' => [
                    'id' => $message->id,
                    'body' => $message->body,
                    'type' => $message->type,
                    'created_at' => $message->created_at->toIso8601String(),
                    'attachment' => [
                        'url' => $message->attachmentUrl(),
                        'name' => $message->attachment_original_name,
                        'mime' => $message->attachment_mime,
                    ],
                    'sender' => [
                        'id' => $message->sender->id,
                        'name' => $message->sender->name,
                    ],
                ],
            ], 201);
        }

        return redirect()->route('messages.show', $user)->with('status', 'message-sent');
    }
}
