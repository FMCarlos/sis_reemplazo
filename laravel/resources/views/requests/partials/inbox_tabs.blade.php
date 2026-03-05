@php
    $queryWithoutTab = request()->except(['tab', 'page']);
@endphp

<div class="d-flex gap-2 flex-wrap">
    @foreach ($tabs as $tabKey => $tab)
        @php
            $tabUrl = route('requests.index', array_merge($queryWithoutTab, ['tab' => $tabKey]));
            $colorClass = $tab['colorClass'] ?? 'secondary';
            $isActive = $activeTab === $tabKey;
        @endphp

        <a
            class="btn btn-sm rounded-pill {{ $isActive ? 'btn-'.$colorClass : 'btn-outline-'.$colorClass }}"
            href="{{ $tabUrl }}"
        >
            {{ $tab['label'] }} <span class="ms-1">({{ $tabCounts[$tabKey] ?? 0 }})</span>
        </a>
    @endforeach
</div>
