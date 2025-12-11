@extends('layout')

@section('title', 'Détails de la Personne')

@section('content')
<div class="d-flex align-items-center mb-4">
    <i class="bi bi-person-circle fs-1 me-3 text-primary"></i>
    <h1 class="mb-0">{{ $person->display_name }}</h1>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted"><i class="bi bi-people-fill"></i> Solde dans les Groupes</h6>
                <h3 class="card-title {{ $person->total_balance < 0 ? 'negative' : 'positive' }}">
                    {{ number_format($person->total_balance, 2) }}€
                </h3>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted"><i class="bi bi-wallet2"></i> Budget Flottant</h6>
                <h3 class="card-title {{ $person->floating_balance < 0 ? 'negative' : 'positive' }}">
                    {{ number_format($person->floating_balance, 2) }}€
                </h3>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 bg-primary bg-opacity-10">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted"><i class="bi bi-cash-stack"></i> Total Global</h6>
                <h3 class="card-title {{ $person->total_balance_with_floating < 0 ? 'negative' : 'positive' }}" style="font-size: 1.8rem;">
                    {{ number_format($person->total_balance_with_floating, 2) }}€
                </h3>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted"><i class="bi bi-calendar-event"></i> Créé le</h6>
                <h3 class="card-title text-info fs-5">{{ $person->created_at->format('d/m/Y H:i') }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Nouvelles statistiques de jeux -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100 bg-danger bg-opacity-10">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted"><i class="bi bi-dice-5-fill"></i> Montant Total Joué</h6>
                <h3 class="card-title negative" style="font-size: 1.8rem;">
                    {{ number_format($totalPlayed, 2) }}€
                </h3>
                <small class="text-muted">Total dépensé dans tous les jeux</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100 bg-success bg-opacity-10">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted"><i class="bi bi-trophy-fill"></i> Montant Total Gagné</h6>
                <h3 class="card-title positive" style="font-size: 1.8rem;">
                    {{ number_format($totalWon, 2) }}€
                </h3>
                <small class="text-muted">Total des gains dans tous les jeux</small>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-info bg-opacity-10 border-0">
        <h5 class="mb-0"><i class="bi bi-wallet2 text-info"></i> Gérer les Fonds Flottants</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <!-- Ajouter des fonds -->
            <div class="col-lg-6">
                <div class="border rounded p-3 h-100">
                    <h6 class="text-success mb-3"><i class="bi bi-plus-circle"></i> Ajouter des Fonds</h6>
                    <form action="{{ route('persons.add-floating-funds', $person) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="add_amount" class="form-label fw-bold">Montant à ajouter</label>
                            <input type="number" step="0.01" min="0.01" id="add_amount" name="amount" class="form-control form-control-sm" placeholder="Ex: 10.00" required>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm w-100">
                            <i class="bi bi-plus-circle me-2"></i>Ajouter
                        </button>
                    </form>
                </div>
            </div>
            <!-- Retirer des fonds -->
            <div class="col-lg-6">
                <div class="border rounded p-3 h-100 {{ $person->floating_balance > 0 ? '' : 'bg-light' }}">
                    <h6 class="text-warning mb-3"><i class="bi bi-dash-circle"></i> Retirer des Fonds</h6>
                    @if($person->floating_balance > 0)
                        <form action="{{ route('persons.withdraw-funds', $person) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="withdraw_amount" class="form-label fw-bold">Montant à retirer (disponible: {{ number_format($person->floating_balance, 2) }} €)</label>
                                <input type="number" step="0.01" min="0.01" max="{{ $person->floating_balance }}" id="withdraw_amount" name="amount" class="form-control form-control-sm" placeholder="Ex: 10.00" required>
                            </div>
                            <button type="submit" class="btn btn-warning btn-sm w-100" onclick="return confirm('Confirmer le retrait de fonds ?')">
                                <i class="bi bi-dash-circle me-2"></i>Retirer
                            </button>
                        </form>
                    @else
                        <div class="alert alert-secondary mb-0">
                            <i class="bi bi-info-circle me-2"></i>Aucun fond disponible pour retrait.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<h2 class="mb-3 mt-4"><i class="bi bi-people text-primary"></i> Groupes</h2>
@if($person->groups->isEmpty())
    <div class="alert alert-info" role="alert">
        <i class="bi bi-info-circle me-2"></i>Cette personne n'appartient à aucun groupe.
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th><i class="bi bi-tag-fill"></i> Nom du Groupe</th>
                    <th><i class="bi bi-wallet"></i> Solde dans le Groupe</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($person->groups as $group)
                <tr>
                    <td>
                        <a href="{{ route('groups.show', $group) }}" class="text-decoration-none fw-bold">
                            <i class="bi bi-people-fill me-1"></i>{{ $group->name }}
                        </a>
                    </td>
                    <td class="{{ $group->pivot->balance < 0 ? 'negative' : 'positive' }} fw-bold">
                        <i class="bi bi-{{ $group->pivot->balance < 0 ? 'dash' : 'plus' }}-circle-fill"></i>
                        {{ number_format($group->pivot->balance, 2) }}€
                    </td>
                    <td class="text-center">
                        <form action="{{ route('groups.remove-person', [$group, $person]) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Retirer du groupe {{ $group->name }} ? Le solde sera transféré vers le budget flottant.')">
                                <i class="bi bi-box-arrow-right"></i> Quitter le Groupe
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<h2 class="mb-3 mt-4"><i class="bi bi-person-plus text-success"></i> Rejoindre un Groupe</h2>
@php
    $availableGroups = \App\Models\Group::whereNotIn('id', $person->groups->pluck('id'))->get();
@endphp

@if($availableGroups->isEmpty())
    <div class="alert alert-warning" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>Tous les groupes disponibles ont déjà été rejoints.
    </div>
@else
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('persons.join-group', $person) }}" method="POST" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-8">
                    <label for="group_id" class="form-label fw-bold">Sélectionner un groupe</label>
                    <select id="group_id" name="group_id" class="form-select form-select-sm" required>
                        <option value="">-- Choisir un groupe --</option>
                        @foreach($availableGroups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-success btn-sm w-100">
                        <i class="bi bi-plus-circle me-2"></i>Rejoindre
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

<h2 class="mb-3 mt-5"><i class="bi bi-clock-history text-info"></i> Historique des Mouvements</h2>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table id="transactionsTable" class="table table-hover align-middle" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th><i class="bi bi-calendar-event"></i> Date</th>
                        <th><i class="bi bi-people-fill"></i> Groupe</th>
                        <th><i class="bi bi-cash"></i> Montant Joué</th>
                        <th><i class="bi bi-person"></i> Coût/Personne</th>
                        <th><i class="bi bi-trophy"></i> Gains Totaux</th>
                        <th><i class="bi bi-person-check"></i> Gain/Personne</th>
                        <th><i class="bi bi-graph-up-arrow"></i> Impact</th>
                        <th class="text-center">Statut</th>
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
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <a href="{{ route('persons.edit', $person) }}" class="btn btn-warning btn-sm">
        <i class="bi bi-pencil-fill me-2"></i>Modifier
    </a>
    <a href="{{ route('persons.index') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-arrow-left-circle me-2"></i>Retour à la liste
    </a>
    <form action="{{ route('persons.destroy', $person) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette personne ?')">
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
    $('#transactionsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('persons.transactions-data', $person) }}",
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
                data: 'group_name',
                orderable: true,
                render: function(data, type, row) {
                    return '<a href="/groups/' + row.group_id + '" class="text-decoration-none fw-bold"><i class="bi bi-people-fill me-1"></i>' + data + '</a>';
                }
            },
            { 
                data: 'amount',
                orderable: true,
                render: function(data) {
                    return '<span class="text-muted">' + data + '</span>';
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
                        return '<span class="positive">' + data + '</span>';
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
                data: 'impact',
                orderable: false,
                render: function(data, type, row) {
                    if (row.impact_value >= 0) {
                        return '<span class="positive fw-bold"><i class="bi bi-arrow-up-circle-fill"></i> ' + data + '</span>';
                    }
                    return '<span class="negative fw-bold"><i class="bi bi-arrow-down-circle-fill"></i> ' + data + '</span>';
                }
            },
            { 
                data: 'is_winner',
                orderable: false,
                className: 'text-center',
                render: function(data) {
                    if (data) {
                        return '<span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Gagné</span>';
                    }
                    return '<span class="badge bg-secondary"><i class="bi bi-x-circle"></i> Pas gagné</span>';
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
                $(api.column(2).footer()).html('<strong class="text-muted">' + json.totals.amount + '</strong>');
                $(api.column(3).footer()).html('<strong class="negative"><i class="bi bi-dash-circle-fill"></i> ' + json.totals.cost_per_person + '</strong>');
                $(api.column(4).footer()).html('<strong class="positive"><i class="bi bi-plus-circle-fill"></i> ' + json.totals.winnings + '</strong>');
                $(api.column(5).footer()).html('<strong class="positive"><i class="bi bi-plus-circle-fill"></i> ' + json.totals.winnings_per_person + '</strong>');
                
                if (json.totals.impact_value >= 0) {
                    $(api.column(6).footer()).html('<strong class="positive"><i class="bi bi-arrow-up-circle-fill"></i> ' + json.totals.impact + '</strong>');
                } else {
                    $(api.column(6).footer()).html('<strong class="negative"><i class="bi bi-arrow-down-circle-fill"></i> ' + json.totals.impact + '</strong>');
                }
            }
        }
    });
});
</script>
@endsection
