<aside class="sidebar">
    <ul class="sidebar-menu list-unstyled">
        <li>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 menu-icon"></i>
                Dashboard
            </a>
        </li>
        <li>
            <a href="{{ route('groups.index') }}" class="{{ request()->routeIs('groups.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill menu-icon"></i>
                Groupes
            </a>
        </li>
        <li>
            <a href="{{ route('persons.index') }}" class="{{ request()->routeIs('persons.*') ? 'active' : '' }}">
                <i class="bi bi-person-fill menu-icon"></i>
                Personnes
            </a>
        </li>
        <li>
            <a href="#" class="menu-toggle active" onclick="toggleSubmenu(event)">
                <i class="bi bi-lightning-fill menu-icon"></i>
                Actions
                <i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul class="submenu list-unstyled active">
                <li>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#playGameModal">
                        <i class="bi bi-dice-5-fill menu-icon"></i>
                        Jouer un Jeu
                    </a>
                </li>
                <li>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#addPersonModal">
                        <i class="bi bi-person-plus-fill menu-icon"></i>
                        + Personne
                    </a>
                </li>
                <li>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#addGroupModal">
                        <i class="bi bi-people-fill menu-icon"></i>
                        + Groupe
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</aside>
