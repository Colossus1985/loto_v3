<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Gestion de Portefeuille')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('Images/faviconLoto.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        h3 {
            margin-bottom: 0px !important;
        }
        
        .app-brand {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 50px;
            background: linear-gradient(90deg, #8ab4ff 0%, #357fff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1001;
            box-shadow: 2px 2px 10px rgba(0,0,0,0.2);
        }

        .app-brand h1 {
            color: white;
            font-size: 1.4rem;
            font-weight: 700;
            margin: 0;
            background: none;
            -webkit-text-fill-color: white;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .app-brand img {
            height: 35px;
            width: auto;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #1e3c72 0%, #2a5298 100%);
            position: fixed;
            top: 50px;
            height: calc(100vh - 50px);
            overflow-y: auto;
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 30px 20px;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .sidebar-header h2 {
            color: white;
            font-size: 22px;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-menu {
            padding: 20px 10px;
        }
        
        .sidebar-menu li {
            margin-bottom: 8px;
        }
        
        .sidebar-menu > li > a {
            display: flex;
            align-items: center;
            padding: 14px 18px;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
            gap: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 14px 18px;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
            gap: 12px;
        }
        
        .sidebar-menu a:hover {
            background: rgba(255,255,255,0.15);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.25);
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .menu-icon {
            font-size: 20px;
            width: 24px;
            text-align: center;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
        }
        
        .content-wrapper {
            background: white;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        }
        
        h1 {
            font-size: 2rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 25px;
        }
        
        h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2d3748;
            margin: 30px 0 20px 0;
        }
        
        .negative {
            color: #e53e3e;
            font-weight: 600;
        }
        
        .positive {
            color: #38a169;
            font-weight: 600;
        }
        
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--card-color, #667eea) 0%, var(--card-color-light, #764ba2) 100%);
        }
        
        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }
        
        .stat-card h3 {
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stat-card h3 i {
            font-size: 1.3rem;
            opacity: 0.8;
        }
        
        .stat-card p {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 0;
            line-height: 1;
        }

        .stat-card small {
            display: block;
            margin-top: 12px;
            font-size: 0.85rem;
            opacity: 0.7;
        }

        /* Card color variants */
        .stat-card.card-primary {
            --card-color: #667eea;
            --card-color-light: #764ba2;
        }

        .stat-card.card-success {
            --card-color: #38a169;
            --card-color-light: #48bb78;
        }

        .stat-card.card-warning {
            --card-color: #ed8936;
            --card-color-light: #f6ad55;
        }

        .stat-card.card-info {
            --card-color: #4299e1;
            --card-color-light: #63b3ed;
        }

        .stat-card.card-danger {
            --card-color: #e53e3e;
            --card-color-light: #fc8181;
        }

        /* Navbar slider */
        .ticker-navbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: 50px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            z-index: 999;
            display: flex;
            align-items: center;
        }

        .slider-wrapper {
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .slider-track {
            display: flex;
            height: 100%;
            align-items: center;
            animation: slideInfinite var(--slide-duration) linear infinite;
        }

        .slider-item {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            padding: 0 50px;
            gap: 10px;
            color: white;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .slider-item:hover {
            transform: scale(1.05);
            filter: brightness(1.2);
        }

        .slider-item i {
            font-size: 1.3rem;
        }

        .slider-item span:first-of-type {
            font-size: 1.1rem;
        }

        .ticker-budget {
            font-weight: 700;
            background: rgba(255,255,255,0.2);
            padding: 0px 15px;
            border-radius: 20px;
            margin-left: 5px;
            font-size: 1rem;
        }

        .ticker-budget.positive {
            background: rgba(245, 255, 250, 0.7);
        }

        .ticker-budget.negative {
            background: rgba(211, 211, 211, 0.7);
        }

        @keyframes slideInfinite {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-50%);
            }
        }

        .slider-track:hover {
            animation-play-state: paused;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: 50px;
            padding: 30px;
            padding-bottom: 100px;
        }

        /* Footer */
        .app-footer {
            position: fixed;
            bottom: 0;
            left: var(--sidebar-width);
            right: 0;
            background: linear-gradient(90deg, #2d3748 0%, #1a202c 100%);
            color: rgba(255,255,255,0.8);
            padding: 15px 30px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            z-index: 998;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .app-footer .footer-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 1.1rem;
            color: white;
        }

        .app-footer .footer-copyright {
            font-size: 0.9rem;
        }

        .app-footer i {
            color: #667eea;
            font-size: 1.3rem;
        }

        /* DataTables footer styling */
        table.dataTable tfoot th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-weight: 600;
            border-top: 2px solid #dee2e6;
            padding-top: 15px !important;
            padding-bottom: 15px !important;
        }

        table.dataTable tfoot {
            border-top: 3px solid #dee2e6;
        }

        /* Submenu Actions */
        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            padding-left: 20px;
        }

        .submenu.active {
            max-height: 300px;
        }

        .submenu a {
            padding: 10px 18px;
            font-size: 0.95rem;
        }

        .menu-toggle {
            cursor: pointer;
        }

        .menu-toggle i.bi-chevron-down {
            transition: transform 0.3s ease;
        }

        .menu-toggle.active i.bi-chevron-down {
            transform: rotate(180deg);
        }
    </style>
</head>
<body>
    <!-- App Brand -->
    <div class="app-brand">
        <h1>
            <img src="{{ asset('Images/LogoLoto.png') }}" alt="Loto de Flo">
        </h1>
    </div>

    <!-- Navbar slider -->
    <div class="ticker-navbar">
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
                            Ajouter une Personne
                        </a>
                    </li>
                    <li>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#addGroupModal">
                            <i class="bi bi-people-fill menu-icon"></i>
                            Ajouter un Groupe
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </aside>
    
    <main class="main-content">
        <div class="content-wrapper">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @yield('content')
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="app-footer">
        <div class="footer-brand">
            <i class="bi bi-dice-5-fill"></i>
            <span>Loto de Flo</span>
        </div>
        <div class="footer-copyright">
            © 2022-{{ date('Y') }} Tous droits réservés
        </div>
    </footer>

    <!-- Modal: Jouer un Jeu -->
    <div class="modal fade" id="playGameModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-dice-5-fill me-2"></i>Jouer un Jeu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('games.store', 0) }}" method="POST" id="playGameForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="play_group_id" class="form-label fw-bold">Groupe <span class="text-danger">*</span></label>
                            <select name="group_id" id="play_group_id" class="form-select" required onchange="updatePlayFormAction(this.value)">
                                <option value="">-- Sélectionner un groupe --</option>
                                @foreach(\App\Models\Group::all() as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }} ({{ $group->persons->count() }} personnes)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="play_amount" class="form-label fw-bold">Montant du Jeu (€) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" id="play_amount" class="form-control" step="0.01" min="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="play_game_date" class="form-label fw-bold">Date du Jeu <span class="text-danger">*</span></label>
                            <input type="date" name="game_date" id="play_game_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-2"></i>Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Ajouter une Personne -->
    <div class="modal fade" id="addPersonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2"></i>Ajouter une Personne</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('persons.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="person_name" class="form-label fw-bold">Nom <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="person_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-2"></i>Créer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Ajouter un Groupe -->
    <div class="modal fade" id="addGroupModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="bi bi-people-fill me-2"></i>Ajouter un Groupe</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('groups.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="group_name" class="form-label fw-bold">Nom du Groupe <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="group_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-info text-white"><i class="bi bi-check-circle me-2"></i>Créer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script>
        function toggleSubmenu(event) {
            event.preventDefault();
            const toggle = event.currentTarget;
            const submenu = toggle.nextElementSibling;
            
            toggle.classList.toggle('active');
            submenu.classList.toggle('active');
        }

        function updatePlayFormAction(groupId) {
            if (groupId) {
                document.getElementById('playGameForm').action = '/groups/' + groupId + '/play';
            }
        }
    </script>
</body>
</html>
