@extends('layout')

@section('title', 'Enregistrer un Gain')

@section('content')
<div class="d-flex align-items-center mb-4">
    <i class="bi bi-trophy-fill fs-1 me-3 text-warning"></i>
    <h1 class="mb-0">Enregistrer un Gain</h1>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-info bg-opacity-10">
        <h3 class="mb-0"><i class="bi bi-info-circle-fill text-info"></i> Informations du Jeu</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-2"><strong><i class="bi bi-people-fill"></i> Groupe:</strong></p>
                <p class="fs-5">{{ $game->group->name }}</p>
            </div>
            <div class="col-md-6">
                <p class="mb-2"><strong><i class="bi bi-calendar-event"></i> Date du Jeu:</strong></p>
                <p class="fs-5">{{ $game->game_date->format('d/m/Y') }}</p>
            </div>
            <div class="col-md-6">
                <p class="mb-2"><strong><i class="bi bi-cash"></i> Montant Joué:</strong></p>
                <p class="negative fs-5">{{ number_format($game->amount, 2) }}€</p>
            </div>
            <div class="col-md-6">
                <p class="mb-2"><strong><i class="bi bi-person"></i> Coût par Personne:</strong></p>
                <p class="negative fs-5">{{ number_format($game->cost_per_person, 2) }}€</p>
            </div>
            <div class="col-md-6">
                <p class="mb-2"><strong><i class="bi bi-hash"></i> Nombre de Personnes:</strong></p>
                <p class="text-primary fs-5">{{ $game->group->persons->count() }}</p>
            </div>
            <div class="col-md-6">
                <p class="mb-2"><strong><i class="bi bi-person-badge"></i> Membres:</strong></p>
                <div>
                    @foreach($game->group->persons as $person)
                        <span class="badge bg-info me-1">{{ $person->display_name }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@if($game->is_winner)
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <strong>Ce jeu a déjà gagné!</strong><br>
        Montant du gain: <strong>{{ number_format($game->winnings, 2) }}€</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <a href="{{ route('groups.show', $game->group) }}" class="btn btn-primary btn-sm">
        <i class="bi bi-arrow-left me-2"></i>Retour au Groupe
    </a>
@else
    <h2 class="mb-3"><i class="bi bi-trophy text-success"></i> Enregistrer le Gain</h2>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('games.record-win', $game) }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label for="winnings" class="form-label fw-bold">
                        <i class="bi bi-cash-coin me-2"></i>Montant du Gain (€) <span class="text-danger">*</span>
                    </label>
                    <input type="number" id="winnings" name="winnings" step="0.01" min="0.01" value="{{ old('winnings') }}" 
                           class="form-control form-control-lg @error('winnings') is-invalid @enderror" 
                           placeholder="Entrez le montant gagné" required>
                    @error('winnings')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="alert alert-success mt-2">
                        <i class="bi bi-calculator"></i> Gain par personne: <strong id="winningsPerPerson" class="fs-5">0.00€</strong>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-check-circle me-2"></i>Enregistrer le Gain
                    </button>
                    <a href="{{ route('groups.show', $game->group) }}" class="btn btn-secondary btn-lg">
                        <i class="bi bi-x-circle me-2"></i>Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const winningsInput = document.getElementById('winnings');
        const winningsDisplay = document.getElementById('winningsPerPerson');
        const personCount = {{ $game->group->persons->count() }};

        winningsInput.addEventListener('input', function() {
            const winnings = parseFloat(this.value) || 0;
            const winningsPerPerson = winnings / personCount;
            winningsDisplay.textContent = winningsPerPerson.toFixed(2) + '€';
        });
    </script>
@endif
@endsection
