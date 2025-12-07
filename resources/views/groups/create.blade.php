@extends('layout')

@section('title', 'Créer un Groupe')

@section('content')
<div class="d-flex align-items-center mb-4">
    <i class="bi bi-people-fill fs-1 me-3 text-success"></i>
    <h1 class="mb-0">Créer un Groupe</h1>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('groups.store') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label for="name" class="form-label fw-bold">
                    <i class="bi bi-tag-fill me-2"></i>Nom du Groupe <span class="text-danger">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" 
                       class="form-control form-control-lg @error('name') is-invalid @enderror" 
                       placeholder="Entrez le nom du groupe" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="bi bi-check-circle me-2"></i>Créer
                </button>
                <a href="{{ route('groups.index') }}" class="btn btn-secondary btn-lg">
                    <i class="bi bi-x-circle me-2"></i>Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
