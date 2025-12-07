@extends('layout')

@section('title', 'Modifier une Personne')

@section('content')
<div class="d-flex align-items-center mb-4">
    <i class="bi bi-pencil-square fs-1 me-3 text-warning"></i>
    <h1 class="mb-0">Modifier une Personne</h1>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('persons.update', $person) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label for="name" class="form-label fw-bold">
                    <i class="bi bi-person-fill me-2"></i>Nom <span class="text-danger">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $person->name) }}" 
                       class="form-control form-control-lg @error('name') is-invalid @enderror" 
                       placeholder="Entrez le nom de la personne" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning btn-lg">
                    <i class="bi bi-check-circle me-2"></i>Mettre à jour
                </button>
                <a href="{{ route('persons.index') }}" class="btn btn-secondary btn-lg">
                    <i class="bi bi-x-circle me-2"></i>Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
