@extends('layout')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex align-items-center mb-4">
    <i class="bi bi-speedometer2 fs-1 me-3 text-primary"></i>
    <h1 class="mb-0">Dashboard - Vue d'ensemble</h1>
</div>

@php
    $totalSpent = \App\Models\Game::sum('amount');
    $totalWon = \App\Models\Game::where('is_winner', true)->sum('winnings');
    $difference = $totalWon - $totalSpent;
@endphp

<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="stat-card card-primary">
            <h3 class="text-primary"><i class="bi bi-people-fill"></i> Total Groupes</h3>
            <p class="text-primary">{{ \App\Models\Group::count() }}</p>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card card-success">
            <h3 class="text-success"><i class="bi bi-person-fill"></i> Total Personnes</h3>
            <p class="text-success">{{ \App\Models\Person::count() }}</p>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card card-warning">
            <h3 class="text-warning"><i class="bi bi-dice-5-fill"></i> Total Jeux</h3>
            <p class="text-warning">{{ \App\Models\Game::count() }}</p>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card card-info">
            <h3 class="text-info"><i class="bi bi-trophy-fill"></i> Jeux Gagnés</h3>
            <p class="text-info">{{ \App\Models\Game::where('is_winner', true)->count() }}</p>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="stat-card card-danger">
            <h3 class="text-danger"><i class="bi bi-cash-coin"></i> Total Joué</h3>
            <p class="text-danger">{{ number_format($totalSpent, 2) }}€</p>
            <small class="text-muted">Montant total dépensé</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card card-success">
            <h3 class="text-success"><i class="bi bi-trophy-fill"></i> Total Gagné</h3>
            <p class="text-success">{{ number_format($totalWon, 2) }}€</p>
            <small class="text-muted">Montant total des gains</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card card-{{ $difference >= 0 ? 'success' : 'danger' }}">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h3 class="text-{{ $difference >= 0 ? 'success' : 'danger' }} mb-0">
                    <i class="bi bi-graph-{{ $difference >= 0 ? 'up' : 'down' }}-arrow"></i> Différence
                </h3>
                <span class="badge bg-{{ $difference >= 0 ? 'success' : 'danger' }} bg-opacity-25 text-{{ $difference >= 0 ? 'success' : 'danger' }} fw-bold px-3 py-2">
                    {{ $difference >= 0 ? 'Bénéfice' : 'Perte' }}
                </span>
            </div>
            <p class="text-{{ $difference >= 0 ? 'success' : 'danger' }}">
                {{ $difference >= 0 ? '+' : '' }}{{ number_format($difference, 2) }}€
            </p>
        </div>
    </div>
</div>

<h2 class="mb-4"><i class="bi bi-bar-chart-line-fill text-primary"></i> Dépenses et Gains par Groupe (par mois)</h2>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <canvas id="groupsChart" style="max-height: 400px;"></canvas>
    </div>
</div>

<h2 class="mb-4 mt-5"><i class="bi bi-person-badge-fill text-success"></i> Soldes par Personne</h2>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <canvas id="personsChart" style="max-height: 400px;"></canvas>
    </div>
</div>

<h2 class="mb-4 mt-5"><i class="bi bi-graph-up text-info"></i> Évolution Globale (Dépenses vs Gains)</h2>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <canvas id="globalChart" style="max-height: 400px;"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Préparer les données pour les graphiques
    const groupsData = @json($groupsData);
    const allMonths = @json($allMonths);
    const persons = @json($persons);

    // Graphique par Groupe
    const groupLabels = allMonths;
    const groupDatasets = [];

    groupsData.forEach((group, index) => {
        const colors = [
            { border: '#007bff', bg: 'rgba(0, 123, 255, 0.1)' },
            { border: '#28a745', bg: 'rgba(40, 167, 69, 0.1)' },
            { border: '#dc3545', bg: 'rgba(220, 53, 69, 0.1)' },
            { border: '#ffc107', bg: 'rgba(255, 193, 7, 0.1)' },
            { border: '#17a2b8', bg: 'rgba(23, 162, 184, 0.1)' },
            { border: '#6f42c1', bg: 'rgba(111, 66, 193, 0.1)' }
        ];
        const color = colors[index % colors.length];

        // Dépenses
        const spentData = allMonths.map(month => {
            return group.data[month] ? -group.data[month].spent : 0;
        });

        // Gains
        const wonData = allMonths.map(month => {
            return group.data[month] ? group.data[month].won : 0;
        });

        groupDatasets.push({
            label: group.name + ' (Dépenses)',
            data: spentData,
            borderColor: color.border,
            backgroundColor: color.bg,
            tension: 0.4,
            borderDash: [5, 5]
        });

        groupDatasets.push({
            label: group.name + ' (Gains)',
            data: wonData,
            borderColor: color.border,
            backgroundColor: color.bg,
            tension: 0.4
        });
    });

    new Chart(document.getElementById('groupsChart'), {
        type: 'line',
        data: {
            labels: groupLabels,
            datasets: groupDatasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Évolution des dépenses et gains par groupe'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value + '€';
                        }
                    }
                }
            }
        }
    });

    // Graphique par Personne (soldes actuels)
    const personNames = persons.map(p => p.name);
    const personBalances = persons.map(p => p.total_balance);
    const personFloating = persons.map(p => p.floating_balance);

    new Chart(document.getElementById('personsChart'), {
        type: 'bar',
        data: {
            labels: personNames,
            datasets: [
                {
                    label: 'Solde dans les Groupes',
                    data: personBalances,
                    backgroundColor: 'rgba(0, 123, 255, 0.7)',
                    borderColor: '#007bff',
                    borderWidth: 1
                },
                {
                    label: 'Budget Flottant',
                    data: personFloating,
                    backgroundColor: 'rgba(23, 162, 184, 0.7)',
                    borderColor: '#17a2b8',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Répartition des soldes par personne'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value + '€';
                        }
                    }
                }
            }
        }
    });

    // Graphique Global (Total dépenses vs total gains)
    const globalSpent = allMonths.map(month => {
        let total = 0;
        groupsData.forEach(group => {
            if (group.data[month]) {
                total += group.data[month].spent;
            }
        });
        return -total;
    });

    const globalWon = allMonths.map(month => {
        let total = 0;
        groupsData.forEach(group => {
            if (group.data[month]) {
                total += group.data[month].won;
            }
        });
        return total;
    });

    new Chart(document.getElementById('globalChart'), {
        type: 'line',
        data: {
            labels: groupLabels,
            datasets: [
                {
                    label: 'Total Dépensé',
                    data: globalSpent,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Total Gagné',
                    data: globalWon,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Évolution globale des dépenses et gains'
                }
            },
            scales: {
                y: {
                    ticks: {
                        callback: function(value) {
                            return value + '€';
                        }
                    }
                }
            }
        }
    });
</script>
@endsection
