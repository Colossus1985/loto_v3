@extends('layout')

@section('title', 'Détails du Groupe')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <i class="bi bi-people-fill fs-1 me-3 text-primary"></i>
        <h1 class="mb-0">{{ $group->name }}</h1>
    </div>
    @if($group->persons->count() > 0)
        <a href="{{ route('games.create', $group) }}" class="btn btn-success btn-sm">
            <i class="bi bi-play-circle me-2"></i>Jouer
        </a>
    @else
        <button class="btn btn-success btn-sm" disabled title="Ajoutez des personnes au groupe pour pouvoir jouer">
            <i class="bi bi-dice-5-fill me-2"></i>Jouer
        </button>
    @endif
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted"><i class="bi bi-dash-circle-fill text-danger"></i> Total Dépensé</h6>
                <h3 class="card-title negative">{{ number_format($group->total_spent, 2) }}€</h3>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted"><i class="bi bi-plus-circle-fill text-success"></i> Total Gagné</h6>
                <h3 class="card-title positive">{{ number_format($group->total_won, 2) }}€</h3>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100 bg-{{ ($group->total_won - $group->total_spent) >= 0 ? 'success' : 'danger' }} bg-opacity-10">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted"><i class="bi bi-graph-{{ ($group->total_won - $group->total_spent) >= 0 ? 'up' : 'down' }}-arrow"></i> Différence</h6>
                <h3 class="card-title {{ ($group->total_won - $group->total_spent) < 0 ? 'negative' : 'positive' }}">{{ number_format($group->total_won - $group->total_spent, 2) }}€</h3>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted"><i class="bi bi-wallet2"></i> Budget Total</h6>
                <h3 class="card-title {{ $group->total_budget < 0 ? 'negative' : 'positive' }}">{{ number_format($group->total_budget, 2) }}€</h3>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted"><i class="bi bi-people"></i> Nombre de Personnes</h6>
                <h3 class="card-title text-primary">{{ $group->persons->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted"><i class="bi bi-calendar-event"></i> Créé le</h6>
                <h3 class="card-title text-info fs-5">{{ $group->created_at->format('d/m/Y H:i') }}</h3>
            </div>
        </div>
    </div>
</div>

<h2 class="mt-5 mb-3"><i class="bi bi-people text-primary"></i> Membres du Groupe</h2>
@if($group->persons->isEmpty())
    <div class="alert alert-info" role="alert">
        <i class="bi bi-info-circle me-2"></i>Aucune personne dans ce groupe.
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nom</th>
                    <th>Solde dans le Groupe</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($group->persons as $person)
                <tr>
                    <td>
                        <a href="{{ route('persons.show', $person) }}" class="text-decoration-none fw-bold">
                            <i class="bi bi-person-fill me-1"></i>{{ $person->display_name }}
                        </a>
                        @if($person->floating_balance > 0)
                            <br><small class="text-info"><i class="bi bi-wallet2"></i> Budget flottant: {{ number_format($person->floating_balance, 2) }}€</small>
                        @endif
                    </td>
                    <td class="{{ $person->pivot->balance < 0 ? 'negative' : 'positive' }} fw-bold">
                        <i class="bi bi-{{ $person->pivot->balance < 0 ? 'dash' : 'plus' }}-circle-fill"></i>
                        {{ number_format($person->pivot->balance, 2) }}€
                    </td>
                    <td>
                        <div class="btn-group mb-2" role="group">
                            <button type="button" class="btn btn-sm btn-success" onclick="document.getElementById('addFundsForm{{ $person->id }}').classList.toggle('d-none')">
                                <i class="bi bi-cash-coin"></i> Ajouter Fonds
                            </button>
                            @if($person->pivot->balance > 0)
                                <button type="button" class="btn btn-sm btn-warning" onclick="document.getElementById('withdrawForm{{ $person->id }}').classList.toggle('d-none')">
                                    <i class="bi bi-dash-circle"></i> Retirer Fonds
                                </button>
                            @endif
                            @if($person->floating_balance > 0)
                                <button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('transferForm{{ $person->id }}').classList.toggle('d-none')">
                                    <i class="bi bi-arrow-down-circle"></i> Transférer du Flottant
                                </button>
                            @endif
                        </div>
                        <form action="{{ route('groups.remove-person', [$group, $person]) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger mb-2" onclick="return confirm('Retirer {{ $person->display_name }} du groupe ? Le solde sera transféré vers le budget flottant.')">
                                <i class="bi bi-person-dash"></i> Retirer
                            </button>
                        </form>
                        
                        <!-- Formulaire d'ajout de fonds -->
                        <div id="addFundsForm{{ $person->id }}" class="d-none mt-2">
                            <div class="card border-success">
                                <div class="card-body bg-success bg-opacity-10">
                                    <h6 class="card-title"><i class="bi bi-cash-stack"></i> Ajouter des fonds externes</h6>
                                    <form action="{{ route('groups.add-funds', [$group, $person]) }}" method="POST" class="row g-2">
                                        @csrf
                                        <div class="col-auto">
                                            <input type="number" name="amount" step="0.01" min="0.01" class="form-control form-control-sm" placeholder="Montant (€)" required>
                                        </div>
                                        <div class="col-auto">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bi bi-check-circle"></i> Ajouter
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('addFundsForm{{ $person->id }}').classList.add('d-none')">
                                                <i class="bi bi-x-circle"></i> Annuler
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Formulaire de retrait de fonds -->
                        @if($person->pivot->balance > 0)
                        <div id="withdrawForm{{ $person->id }}" class="d-none mt-2">
                            <div class="card border-warning">
                                <div class="card-body bg-warning bg-opacity-10">
                                    <h6 class="card-title"><i class="bi bi-cash-stack"></i> Retirer des fonds du groupe</h6>
                                    <small class="text-muted d-block mb-2">{{ number_format($person->pivot->balance, 2) }}€ disponibles</small>
                                    <form action="{{ route('groups.withdraw-funds', [$group, $person]) }}" method="POST" class="row g-2">
                                        @csrf
                                        <div class="col-auto">
                                            <input type="number" name="amount" step="0.01" min="0.01" max="{{ $person->pivot->balance }}" class="form-control form-control-sm" placeholder="Montant (€)" required>
                                        </div>
                                        <div class="col-auto">
                                            <button type="submit" class="btn btn-sm btn-warning">
                                                <i class="bi bi-check-circle"></i> Retirer
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('withdrawForm{{ $person->id }}').classList.add('d-none')">
                                                <i class="bi bi-x-circle"></i> Annuler
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Formulaire de transfert depuis le flottant -->
                        @if($person->floating_balance > 0)
                        <div id="transferForm{{ $person->id }}" class="d-none mt-2">
                            <div class="card border-primary">
                                <div class="card-body bg-primary bg-opacity-10">
                                    <h6 class="card-title"><i class="bi bi-arrow-down-up"></i> Transférer du budget flottant</h6>
                                    <small class="text-muted d-block mb-2">{{ number_format($person->floating_balance, 2) }}€ disponibles</small>
                                    <form action="{{ route('groups.transfer-from-floating', [$group, $person]) }}" method="POST" class="row g-2">
                                        @csrf
                                        <div class="col-auto">
                                            <input type="number" name="amount" step="0.01" min="0.01" max="{{ $person->floating_balance }}" class="form-control form-control-sm" placeholder="Montant (€)" required>
                                        </div>
                                        <div class="col-auto">
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="bi bi-check-circle"></i> Transférer
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('transferForm{{ $person->id }}').classList.add('d-none')">
                                                <i class="bi bi-x-circle"></i> Annuler
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<h2 class="mt-4 mb-3"><i class="bi bi-person-plus text-success"></i> Ajouter une Personne</h2>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('groups.add-person', $group) }}" method="POST" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-8">
                <label for="person_id" class="form-label fw-bold">Sélectionner une personne</label>
                <select id="person_id" name="person_id" class="form-select form-select-sm" required>
                    <option value="">-- Choisir une personne --</option>
                    @foreach($availablePersons as $person)
                        <option value="{{ $person->id }}">{{ $person->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-success btn-sm w-100">
                    <i class="bi bi-plus-circle me-2"></i>Ajouter
                </button>
            </div>
        </form>
    </div>
</div>

<h2 class="mt-5 mb-3"><i class="bi bi-receipt text-info"></i> Historique des Transactions</h2>
@if($transactions->isEmpty())
    <div class="alert alert-info" role="alert">
        <i class="bi bi-info-circle me-2"></i>Aucune transaction enregistrée pour ce groupe.
    </div>
@else
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th><i class="bi bi-calendar-event"></i> Date</th>
                            <th><i class="bi bi-person-fill"></i> Personne</th>
                            <th><i class="bi bi-tag"></i> Type</th>
                            <th class="text-end"><i class="bi bi-cash"></i> Montant</th>
                            <th class="text-end"><i class="bi bi-graph-down"></i> Solde Avant</th>
                            <th class="text-end"><i class="bi bi-graph-up"></i> Solde Après</th>
                            <th><i class="bi bi-chat-left-text"></i> Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                        <tr>
                            <td>
                                <small class="text-muted">
                                    {{ $transaction->created_at->format('d/m/Y H:i') }}
                                </small>
                            </td>
                            <td>
                                <a href="{{ route('persons.show', $transaction->person) }}" class="text-decoration-none">
                                    <i class="bi bi-person-fill"></i> {{ $transaction->person->display_name }}
                                </a>
                            </td>
                            <td>
                                @php
                                    $typeConfig = [
                                        'add_floating' => ['icon' => 'plus-circle-fill', 'color' => 'success', 'label' => 'Ajout Flottant'],
                                        'withdraw_floating' => ['icon' => 'dash-circle-fill', 'color' => 'danger', 'label' => 'Retrait Flottant'],
                                        'join_group' => ['icon' => 'box-arrow-in-right', 'color' => 'primary', 'label' => 'Adhésion'],
                                        'leave_group' => ['icon' => 'box-arrow-right', 'color' => 'warning', 'label' => 'Départ'],
                                        'add_group_funds' => ['icon' => 'piggy-bank-fill', 'color' => 'info', 'label' => 'Ajout Fonds'],
                                        'withdraw_group_funds' => ['icon' => 'cash-stack', 'color' => 'warning', 'label' => 'Retrait Fonds'],
                                        'transfer_to_group' => ['icon' => 'arrow-left-right', 'color' => 'secondary', 'label' => 'Transfert'],
                                        'game_played' => ['icon' => 'dice-5-fill', 'color' => 'danger', 'label' => 'Jeu Joué'],
                                        'game_won' => ['icon' => 'trophy-fill', 'color' => 'success', 'label' => 'Gain'],
                                    ];
                                    $config = $typeConfig[$transaction->type] ?? ['icon' => 'question-circle', 'color' => 'secondary', 'label' => $transaction->type];
                                @endphp
                                <span class="badge bg-{{ $config['color'] }}">
                                    <i class="bi bi-{{ $config['icon'] }}"></i> {{ $config['label'] }}
                                </span>
                            </td>
                            <td class="text-end">
                                <strong class="{{ $transaction->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                    <i class="bi bi-{{ $transaction->amount >= 0 ? 'plus' : 'dash' }}-circle-fill"></i>
                                    {{ number_format(abs($transaction->amount), 2) }}€
                                </strong>
                            </td>
                            <td class="text-end">
                                <span class="text-muted">{{ number_format($transaction->balance_before, 2) }}€</span>
                            </td>
                            <td class="text-end">
                                <strong class="{{ $transaction->balance_after >= $transaction->balance_before ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($transaction->balance_after, 2) }}€
                                </strong>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $transaction->description ?? '-' }}
                                </small>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

<h2 class="mt-5 mb-3"><i class="bi bi-clock-history text-info"></i> Historique des Jeux</h2>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table id="gamesTable" class="table table-hover align-middle" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th><i class="bi bi-calendar-event"></i> Date du Jeu</th>
                        <th><i class="bi bi-cash"></i> Montant Joué</th>
                        <th><i class="bi bi-person"></i> Coût/Personne</th>
                        <th><i class="bi bi-trophy"></i> Gains</th>
                        <th><i class="bi bi-person-check"></i> Gain/Personne</th>
                        <th class="text-center">Statut</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tfoot class="table-light">
                    <tr>
                        <th class="text-end">Total :</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <a href="{{ route('groups.edit', $group) }}" class="btn btn-warning">
        <i class="bi bi-pencil-fill me-2"></i>Modifier
    </a>
    <a href="{{ route('groups.index') }}" class="btn btn-primary">
        <i class="bi bi-arrow-left-circle me-2"></i>Retour à la liste
    </a>
    <form action="{{ route('groups.destroy', $group) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce groupe ?')">
            <i class="bi bi-trash-fill me-2"></i>Supprimer
        </button>
    </form>
</div>

<!-- DataTables CSS et JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.0/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.0/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.0/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#gamesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('groups.games-data', $group) }}",
            type: 'GET'
        },
        columns: [
            { 
                data: 'game_date',
                orderable: true,
                render: function(data) {
                    return '<strong>' + data + '</strong>';
                }
            },
            { 
                data: 'amount',
                orderable: true,
                render: function(data) {
                    return '<span class="negative"><i class="bi bi-dash-circle-fill"></i> ' + data + '</span>';
                }
            },
            { 
                data: 'cost_per_person',
                orderable: true,
                render: function(data) {
                    return '<span class="negative"><i class="bi bi-dash-circle-fill"></i> ' + data + '</span>';
                }
            },
            { 
                data: 'winnings',
                orderable: true,
                render: function(data, type, row) {
                    if (row.is_winner) {
                        return '<span class="positive"><i class="bi bi-plus-circle-fill"></i> ' + data + '</span>';
                    }
                    return '<span class="text-muted">-</span>';
                }
            },
            { 
                data: 'winnings_per_person',
                orderable: false,
                render: function(data, type, row) {
                    if (row.is_winner) {
                        return '<span class="positive"><i class="bi bi-plus-circle-fill"></i> ' + data + '</span>';
                    }
                    return '<span class="text-muted">-</span>';
                }
            },
            { 
                data: 'status',
                orderable: true,
                className: 'text-center',
                render: function(data, type, row) {
                    if (row.is_winner) {
                        return '<span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Gagné</span>';
                    }
                    return '<span class="badge bg-secondary"><i class="bi bi-x-circle"></i> Pas gagné</span>';
                }
            },
            { 
                data: 'actions',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    if (!row.is_winner && data) {
                        return '<a href="' + data + '" class="btn btn-sm btn-success"><i class="bi bi-trophy-fill"></i> Enregistrer Gain</a>';
                    }
                    return '';
                }
            }
        ],
        order: [[0, 'desc']],
        language: {
            processing: "Traitement en cours...",
            search: "Rechercher&nbsp;:",
            lengthMenu: "Afficher _MENU_ éléments",
            info: "Affichage de l'élément _START_ à _END_ sur _TOTAL_ éléments",
            infoEmpty: "Affichage de l'élément 0 à 0 sur 0 élément",
            infoFiltered: "(filtré de _MAX_ éléments au total)",
            infoPostFix: "",
            loadingRecords: "Chargement en cours...",
            zeroRecords: "Aucun élément à afficher",
            emptyTable: "Aucune donnée disponible dans le tableau",
            paginate: {
                first: "Premier",
                previous: "Précédent",
                next: "Suivant",
                last: "Dernier"
            },
            aria: {
                sortAscending: ": activer pour trier la colonne par ordre croissant",
                sortDescending: ": activer pour trier la colonne par ordre décroissant"
            }
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        footerCallback: function(row, data, start, end, display) {
            var api = this.api();
            
            // Get totals from server response
            var json = api.ajax.json();
            if (json && json.totals) {
                $(api.column(1).footer()).html('<strong class="negative"><i class="bi bi-dash-circle-fill"></i> ' + json.totals.amount + '</strong>');
                $(api.column(2).footer()).html('<strong class="negative"><i class="bi bi-dash-circle-fill"></i> ' + json.totals.cost_per_person + '</strong>');
                $(api.column(3).footer()).html('<strong class="positive"><i class="bi bi-plus-circle-fill"></i> ' + json.totals.winnings + '</strong>');
                $(api.column(4).footer()).html('<strong class="positive"><i class="bi bi-plus-circle-fill"></i> ' + json.totals.winnings_per_person + '</strong>');
            }
        }
    });
});
</script>
@endsection
