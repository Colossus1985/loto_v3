<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loto de Flo</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('Images/faviconLoto.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>
<body>
    <!-- App Brand -->
    <a href="{{ route('groups.index') }}" class="app-brand" style="text-decoration: none;">
        <h1>
            <img src="{{ asset('Images/LogoLoto.png') }}" alt="Loto de Flo" class="brand-logo">
            <img src="{{ asset('Images/faviconLoto.ico') }}" alt="Loto de Flo" class="brand-favicon">
        </h1>
    </a>

    @include('partials.navbar')

    @include('partials.sidebar')
    
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
    
    @include('partials.footer')

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
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check-circle me-2"></i>Enregistrer</button>
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
                        <div class="row mb-3">
                            <div class="col-6">
                                <label for="person_firstname" class="form-label fw-bold">Prénom</label>
                                <input type="text" name="firstname" id="person_firstname" class="form-control" placeholder="Prénom">
                            </div>
                            <div class="col-6">
                                <label for="person_lastname" class="form-label fw-bold">Nom de famille</label>
                                <input type="text" name="lastname" id="person_lastname" class="form-control" placeholder="Nom de famille">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="person_pseudo" class="form-label fw-bold">Pseudo <span class="text-muted">(affiché partout)</span></label>
                            <input type="text" name="pseudo" id="person_pseudo" class="form-control" placeholder="Pseudo d'affichage">
                            <small class="form-text text-muted">Le pseudo sera affiché à la place du nom/prénom</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-circle me-2"></i>Créer</button>
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
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-info btn-sm text-white"><i class="bi bi-check-circle me-2"></i>Créer</button>
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

        function toggleSidebar() {
            document.body.classList.toggle('sidebar-collapsed');
            // Sauvegarder l'état dans localStorage
            const isCollapsed = document.body.classList.contains('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        }

        // Restaurer l'état de la sidebar au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed) {
                document.body.classList.add('sidebar-collapsed');
            }
        });
    </script>
</body>
</html>
