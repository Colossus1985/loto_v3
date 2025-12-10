@extends('layout')

@section('title', 'Jouer - ' . $group->name)

@section('content')
<div class="d-flex align-items-center mb-4">
    <i class="bi bi-dice-5-fill fs-1 me-3 text-success"></i>
    <h1 class="mb-0">Jouer - {{ $group->name }}</h1>
</div>

@if($group->persons->isEmpty())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Erreur:</strong> Aucune personne dans le groupe. Veuillez ajouter des personnes avant de jouer.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <a href="{{ route('groups.show', $group) }}" class="btn btn-primary btn-sm">
        <i class="bi bi-arrow-left me-2"></i>Retour au Groupe
    </a>
@else
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-primary bg-opacity-10">
            <h3 class="mb-0"><i class="bi bi-info-circle-fill text-primary"></i> Informations du Groupe</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p class="mb-2"><strong><i class="bi bi-wallet2"></i> Budget Total:</strong></p>
                    <p class="{{ $group->total_budget < 0 ? 'negative' : 'positive' }} fs-4 fw-bold">
                        {{ number_format($group->total_budget, 2) }}€
                    </p>
                </div>
                <div class="col-md-4">
                    <p class="mb-2"><strong><i class="bi bi-people-fill"></i> Nombre de Personnes:</strong></p>
                    <p class="text-primary fs-4 fw-bold">{{ $group->persons->count() }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-2"><strong><i class="bi bi-person-badge"></i> Membres:</strong></p>
                    <div>
                        @foreach($group->persons as $person)
                            <span class="badge bg-info me-1">{{ $person->name }} ({{ number_format($person->pivot->balance, 2) }}€)</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h2 class="mb-3"><i class="bi bi-controller text-success"></i> Enregistrer un Jeu</h2>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('games.store', $group) }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label for="amount" class="form-label fw-bold">
                        <i class="bi bi-cash-coin me-2"></i>Montant du Jeu (€) <span class="text-danger">*</span>
                    </label>
                    <input type="number" id="amount" name="amount" step="0.01" min="0.01" value="{{ old('amount') }}" 
                           class="form-control form-control-lg @error('amount') is-invalid @enderror" 
                           placeholder="Entrez le montant" required>
                    @error('amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="alert alert-info mt-2">
                        <i class="bi bi-calculator"></i> Coût par personne: <strong id="costPerPerson" class="fs-5">0.00€</strong>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="game_date" class="form-label fw-bold">
                        <i class="bi bi-calendar-event me-2"></i>Date du Jeu <span class="text-danger">*</span>
                    </label>
                    <input type="date" id="game_date" name="game_date" value="{{ old('game_date', date('Y-m-d')) }}" 
                           class="form-control form-control-lg @error('game_date') is-invalid @enderror" required>
                    @error('game_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-check-circle me-2"></i>Enregistrer le Jeu
                    </button>
                    <a href="{{ route('groups.show', $group) }}" class="btn btn-secondary btn-lg">
                        <i class="bi bi-x-circle me-2"></i>Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const amountInput = document.getElementById('amount');
        const costDisplay = document.getElementById('costPerPerson');
        const personCount = {{ $group->persons->count() }};

        amountInput.addEventListener('input', function() {
            const amount = parseFloat(this.value) || 0;
            const costPerPerson = amount / personCount;
            costDisplay.textContent = costPerPerson.toFixed(2) + '€';
        });
    </script>
@endif
@endsection
