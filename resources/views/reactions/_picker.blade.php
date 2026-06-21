@php
    use App\Models\Message;
    use App\Models\Post;
    use App\Models\Reaction;

    $route = $subject instanceof Post
        ? route('posts.reactions.store', $subject)
        : route('messages.reactions.store', $subject);
    $currentKind = $subject->currentReactionKindFor(auth()->user());
    $summary = $subject->reactionSummary();
@endphp

<div class="reaction-picker" data-reaction-picker data-reaction-url="{{ $route }}">
    @foreach (Reaction::options() as $kind => $option)
        <button
            type="button"
            class="reaction-chip {{ $currentKind === $kind ? 'is-active' : '' }}"
            data-reaction-kind="{{ $kind }}"
            aria-pressed="{{ $currentKind === $kind ? 'true' : 'false' }}"
            title="{{ $option['label'] }}"
        >
            @php
                $count = $summary[$kind] ?? 0;
            @endphp
            <span class="reaction-symbol">{{ $option['symbol'] }}</span>
            <span class="reaction-count">{{ $count > 0 ? $count : '' }}</span>
        </button>
    @endforeach
</div>
