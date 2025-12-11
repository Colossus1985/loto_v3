@extends('layout')

@section('title', 'Créer une Personne')

@section('content')
<div class="d-flex align-items-center mb-4">
    <i class="bi bi-person-plus-fill fs-1 me-3 text-success"></i>
    <h1 class="mb-0">Créer une Personne</h1>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('persons.store') }}" method="POST">
            @csrf

            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="firstname" class="form-label fw-bold">
                        <i class="bi bi-person-fill me-2"></i>Prénom
                    </label>
                    <input type="text" id="firstname" name="firstname" value="{{ old('firstname') }}" 
                           class="form-control form-control-sm @error('firstname') is-invalid @enderror" 
                           placeholder="Prénom">
                    @error('firstname')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="lastname" class="form-label fw-bold">
                        <i class="bi bi-person-fill me-2"></i>Nom de famille
                    </label>
                    <input type="text" id="lastname" name="lastname" value="{{ old('lastname') }}" 
                           class="form-control form-control-sm @error('lastname') is-invalid @enderror" 
                           placeholder="Nom de famille">
                    @error('lastname')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="pseudo" class="form-label fw-bold">
                    <i class="bi bi-star-fill me-2"></i>Pseudo <span class="text-muted">(affiché partout)</span>
                </label>
                <input type="text" id="pseudo" name="pseudo" value="{{ old('pseudo') }}" 
                       class="form-control form-control-sm @error('pseudo') is-invalid @enderror" 
                       placeholder="Pseudo d'affichage">
                @error('pseudo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Si renseigné, le pseudo sera affiché à la place du nom/prénom</small>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="bi bi-check-circle me-2"></i>Créer
                </button>
                <a href="{{ route('persons.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-x-circle me-2"></i>Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
