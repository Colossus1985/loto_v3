@extends('layout')

@section('title', 'Liste des Groupes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <i class="bi bi-people-fill fs-1 me-3 text-primary"></i>
        <h1 class="mb-0">Liste des Groupes</h1>
    </div>
    <a href="{{ route('groups.create') }}" class="btn btn-success btn-lg">
        <i class="bi bi-plus-circle me-2"></i>Créer un Groupe
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
@endsection
