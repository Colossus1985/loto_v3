@extends('layout')

@section('title', 'Liste des Groupes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <i class="bi bi-people-fill fs-1 me-3 text-primary"></i>
        <h1 class="mb-0">Liste des Groupes</h1>
    </div>
    <a href="{{ route('groups.create') }}" class="btn btn-success btn-sm">
        <i class="bi bi-plus-circle me-2"></i>Ajouter un Groupe
    </a>
</div>

@if($groups->isEmpty())
    <div class="alert alert-info" role="alert">
        <i class="bi bi-info-circle me-2"></i>Aucun groupe enregistré.
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Total Dépensé</th>
                    <th>Total Gagné</th>
                    <th>Budget Total</th>
                    <th>Nb Personnes</th>
                    <th>Personnes</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($groups as $group)
                <tr>
                    <td><span class="badge bg-secondary">{{ $group->id }}</span></td>
                    <td>
                        <a href="{{ route('groups.show', $group) }}" class="text-decoration-none fw-bold text-dark">
                            <i class="bi bi-people-fill me-1 text-primary"></i>{{ $group->name }}
                        </a>
                    </td>
                    <td class="negative">
                        <i class="bi bi-dash-circle-fill me-1"></i>{{ number_format($group->total_spent, 2) }}€
                    </td>
                    <td class="positive">
                        <i class="bi bi-plus-circle-fill me-1"></i>{{ number_format($group->total_won, 2) }}€
                    </td>
                    <td class="{{ $group->total_budget < 0 ? 'negative' : 'positive' }}">
                        <strong>{{ number_format($group->total_budget, 2) }}€</strong>
                    </td>
                    <td><span class="badge bg-info">{{ $group->persons_count }}</span></td>
                    <td>
                        @if($group->persons->isEmpty())
                            <em class="text-muted">Aucune personne</em>
                        @else
                            @foreach($group->persons as $person)
                                <a href="{{ route('persons.show', $person) }}" class="badge bg-primary text-white text-decoration-none me-1">
                                    <i class="bi bi-person-fill"></i> {{ $person->name }}
                                </a>
                            @endforeach
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            <a href="{{ route('groups.show', $group) }}" class="btn btn-sm btn-primary" title="Voir">
                                <i class="bi bi-eye-fill"></i>
                            </a>
                            <a href="{{ route('games.create', $group) }}" class="btn btn-sm btn-success" title="Jouer">
                                <i class="bi bi-dice-5-fill"></i>
                            </a>
                            <a href="{{ route('groups.edit', $group) }}" class="btn btn-sm btn-warning" title="Modifier">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                        </div>
                        <form action="{{ route('groups.destroy', $group) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger ms-1" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce groupe ?')" title="Supprimer">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@php
    $trashedGroups = \App\Models\Group::onlyTrashed()->withCount('persons')->get();
@endphp

@if($trashedGroups->isNotEmpty())
<div class="mt-5">
    <details class="mb-4">
        <summary class="h3 cursor-pointer d-flex align-items-center gap-2">
            <i class="bi bi-archive text-muted"></i> 
            Groupes Supprimés ({{ $trashedGroups->count() }})
            <i class="bi bi-chevron-down ms-2"></i>
        </summary>
        
        <div class="table-responsive mt-3">
            <table class="table table-hover align-middle bg-light bg-opacity-50">
                <thead class="table-secondary">
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Total Dépensé</th>
                        <th>Total Gagné</th>
                        <th>Budget Total</th>
                        <th>Nb Personnes</th>
                        <th>Supprimé le</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($trashedGroups as $group)
                    <tr class="text-muted">
                        <td><span class="badge bg-secondary">{{ $group->id }}</span></td>
                        <td>
                            <i class="bi bi-people-fill me-1"></i>{{ $group->name }}
                        </td>
                        <td>{{ number_format($group->total_spent, 2) }}€</td>
                        <td>{{ number_format($group->total_won, 2) }}€</td>
                        <td><strong>{{ number_format($group->total_budget, 2) }}€</strong></td>
                        <td><span class="badge bg-info">{{ $group->persons_count }}</span></td>
                        <td>{{ $group->deleted_at->format('d/m/Y H:i') }}</td>
                        <td class="text-center">
                            <form action="{{ route('groups.restore', $group->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" title="Restaurer">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Restaurer
                                </button>
                            </form>
                            <form action="{{ route('groups.force-destroy', $group->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger ms-1" onclick="return confirm('Supprimer définitivement ce groupe ? Cette action est irréversible !')" title="Supprimer définitivement">
                                    <i class="bi bi-trash3-fill me-1"></i>Supprimer définitivement
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </details>
</div>
@endif
@endsection
