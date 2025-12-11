<aside class="sidebar">
    <ul class="sidebar-menu list-unstyled">
        <li>
            <!-- <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" title="Dashboard"> -->
            <!-- <i class="bi bi-speedometer2 menu-icon"></i> -->
                <!-- <span>DASHBOARD</span> -->
            <!-- </a> -->
        </li>
        <li>
            <a href="{{ route('groups.index') }}" class="{{ request()->routeIs('groups.*') ? 'active' : '' }}" title="Groupes">
                <i class="bi bi-people-fill menu-icon"></i>
                <span>Groupes</span>
            </a>
        </li>
        <li>
            <a href="{{ route('persons.index') }}" class="{{ request()->routeIs('persons.*') ? 'active' : '' }}" title="Personnes">
                <i class="bi bi-person-fill menu-icon"></i>
                <span>Personnes</span>
            </a>
        </li>
        <li>
            <a href="#" class="menu-toggle active" onclick="toggleSubmenu(event)" title="Actions">
                <i class="bi bi-lightning-fill menu-icon"></i>
                <span>Actions</span>
                <i class="bi bi-chevron-down ms-auto chevron-icon"></i>
            </a>
            <ul class="submenu list-unstyled active">
                <li>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#playGameModal" title="Jouer un Jeu">
                        <i class="bi bi-dice-5-fill menu-icon"></i>
                        <span>Jouer un Jeu</span>
                    </a>
                </li>
                <li>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#addPersonModal" title="Ajouter une Personne">
                        <i class="bi bi-person-plus-fill menu-icon"></i>
                        <span>+ Personne</span>
                    </a>
                </li>
                <li>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#addGroupModal" title="Ajouter un Groupe">
                        <i class="bi bi-people-fill menu-icon"></i>
                        <span>+ Groupe</span>
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</aside>
