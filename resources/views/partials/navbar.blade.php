<!-- Navbar slider -->
<div class="ticker-navbar">
    <button class="sidebar-toggle" onclick="toggleSidebar()" title="Replier/Déplier la sidebar">
        <i class="bi bi-list"></i>
    </button>
    <div class="slider-wrapper">
        @php
            $groups = \App\Models\Group::all();
            $slideDuration = max(20, $groups->count() * 5);
        @endphp
        <div class="slider-track" style="--slide-duration: {{ $slideDuration }}s;">
            @foreach($groups as $group)
                <a href="{{ route('groups.show', $group) }}" class="slider-item">
                    <i class="bi bi-people-fill"></i>
                    <span>{{ $group->name }}</span>
                    <span class="ticker-budget {{ $group->total_budget < 0 ? 'negative' : 'positive' }}">
                        {{ number_format($group->total_budget, 2) }}€
                    </span>
                </a>
            @endforeach
            <!-- Duplication pour boucle continue -->
            @foreach($groups as $group)
                <a href="{{ route('groups.show', $group) }}" class="slider-item">
                    <i class="bi bi-people-fill"></i>
                    <span>{{ $group->name }}</span>
                    <span class="ticker-budget {{ $group->total_budget < 0 ? 'negative' : 'positive' }}">
                        {{ number_format($group->total_budget, 2) }}€
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</div>
