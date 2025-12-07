@extends('layout')

@section('title', 'Liste des Personnes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <i class="bi bi-person-fill fs-1 me-3 text-success"></i>
        <h1 class="mb-0">Liste des Personnes</h1>
    </div>
    <a href="{{ route('persons.create') }}" class="btn btn-success btn-lg">
        <i class="bi bi-plus-circle me-2"></i>Ajouter une Personne
    </a>
</div>

@if($persons->isEmpty())
    <div class="alert alert-info" role="alert">
        <i class="bi bi-info-circle me-2"></i>Aucune personne enregistrée.
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Solde Groupes</th>
                    <th>Budget Flottant</th>
                    <th>Total</th>
                    <th>Groupes</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($persons as $person)
                <tr>
                    <td><span class="badge bg-secondary">{{ $person->id }}</span></td>
                    <td>
                        <a href="{{ route('persons.show', $person) }}" class="text-decoration-none fw-bold">
                            <i class="bi bi-person-fill me-1"></i>{{ $person->name }}
                        </a>
                    </td>
                    <td class="{{ $person->total_balance < 0 ? 'negative' : 'positive' }}">
                        <i class="bi bi-{{ $person->total_balance < 0 ? 'dash' : 'plus' }}-circle-fill me-1"></i>
                        {{ number_format($person->total_balance, 2) }}€
                    </td>
                    <td class="{{ $person->floating_balance < 0 ? 'negative' : 'positive' }}">
                        <i class="bi bi-{{ $person->floating_balance < 0 ? 'dash' : 'plus' }}-circle-fill me-1"></i>
                        {{ number_format($person->floating_balance, 2) }}€
                    </td>
                    <td class="{{ $person->total_balance_with_floating < 0 ? 'negative' : 'positive' }}">
                        <strong>{{ number_format($person->total_balance_with_floating, 2) }}€</strong>
                    </td>
                    <td>
                        @if($person->groups->isEmpty())
                            <em class="text-muted">Aucun groupe</em>
                        @else
                            @foreach($person->groups as $group)
                                <a href="{{ route('groups.show', $group) }}" class="badge bg-primary text-white text-decoration-none me-1">
                                    <i class="bi bi-people-fill"></i> {{ $group->name }} ({{ number_format($group->pivot->balance, 2) }}€)
                                </a>
                            @endforeach
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            <a href="{{ route('persons.show', $person) }}" class="btn btn-sm btn-primary" title="Voir">
                                <i class="bi bi-eye-fill"></i>
                            </a>
                            <a href="{{ route('persons.edit', $person) }}" class="btn btn-sm btn-warning" title="Modifier">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                        </div>
                        <form action="{{ route('persons.destroy', $person) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger ms-1" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette personne ?')" title="Supprimer">
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
