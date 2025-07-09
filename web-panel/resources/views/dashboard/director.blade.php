@extends('layouts.app')

@section('title', 'Дашборд директора')

@section('content')
<div class="row g-4">
    <!-- Main Statistics -->
    <div class="col-md-3">
        <div class="stats-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="text-muted mb-2">Филиалы</h6>
                    <h3 class="mb-0">{{ $totalStats['branches'] }}</h3>
                </div>
                <div class="bg-primary bg-opacity-10 p-3 rounded">
                    <i class="bi bi-building text-primary fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="text-muted mb-2">Всего заявок</h6>
                    <h3 class="mb-0">{{ $totalStats['total_repairs'] }}</h3>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i>
                        +{{ $monthlyStats['repairs_this_month'] }} в этом месяце
                    </small>
                </div>
                <div class="bg-warning bg-opacity-10 p-3 rounded">
                    <i class="bi bi-tools text-warning fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="text-muted mb-2">Картриджи</h6>
                    <h3 class="mb-0">{{ $totalStats['total_cartridges'] }}</h3>
                    <small class="text-info">
                        <i class="bi bi-arrow-up"></i>
                        +{{ $monthlyStats['cartridges_this_month'] }} в этом месяце
                    </small>
                </div>
                <div class="bg-info bg-opacity-10 p-3 rounded">
                    <i class="bi bi-printer text-info fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="text-muted mb-2">Инвентарь</h6>
                    <h3 class="mb-0">{{ $totalStats['total_inventory'] }}</h3>
                </div>
                <div class="bg-success bg-opacity-10 p-3 rounded">
                    <i class="bi bi-pc-display text-success fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <!-- Monthly Trends Chart -->
    <div class="col-lg-8">
        <div class="stats-card p-4">
            <h5 class="card-title mb-3">Динамика заявок</h5>
            <canvas id="monthlyChart" height="100"></canvas>
        </div>
    </div>
    
    <!-- Status Distribution -->
    <div class="col-lg-4">
        <div class="stats-card p-4 h-100">
            <h5 class="card-title mb-3">Распределение по статусам</h5>
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <!-- Top Branches -->
    <div class="col-lg-6">
        <div class="stats-card p-4">
            <h5 class="card-title mb-3">Топ филиалы по активности</h5>
            
            @foreach($topBranches as $branch)
            <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                <div>
                    <h6 class="mb-1">{{ $branch->name }}</h6>
                    <small class="text-muted">{{ $branch->repair_requests_count }} заявок</small>
                </div>
                <div class="text-end">
                    <span class="badge bg-primary">{{ $branch->cartridge_replacements_count }} картриджей</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    
    <!-- Monthly Comparison -->
    <div class="col-lg-6">
        <div class="stats-card p-4">
            <h5 class="card-title mb-3">Сравнение с прошлым месяцем</h5>
            
            <div class="row g-3">
                <div class="col-6">
                    <div class="text-center p-3 bg-light rounded">
                        <h4 class="text-primary">{{ $monthlyStats['repairs_this_month'] }}</h4>
                        <small class="text-muted">Заявки в этом месяце</small>
                        @php
                            $repairChange = $monthlyStats['repairs_last_month'] > 0 
                                ? (($monthlyStats['repairs_this_month'] - $monthlyStats['repairs_last_month']) / $monthlyStats['repairs_last_month']) * 100 
                                : 0;
                        @endphp
                        <div class="mt-1">
                            <small class="{{ $repairChange >= 0 ? 'text-success' : 'text-danger' }}">
                                <i class="bi bi-arrow-{{ $repairChange >= 0 ? 'up' : 'down' }}"></i>
                                {{ number_format(abs($repairChange), 1) }}%
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="col-6">
                    <div class="text-center p-3 bg-light rounded">
                        <h4 class="text-info">{{ $monthlyStats['cartridges_this_month'] }}</h4>
                        <small class="text-muted">Картриджи в этом месяце</small>
                        @php
                            $cartridgeChange = $monthlyStats['cartridges_last_month'] > 0 
                                ? (($monthlyStats['cartridges_this_month'] - $monthlyStats['cartridges_last_month']) / $monthlyStats['cartridges_last_month']) * 100 
                                : 0;
                        @endphp
                        <div class="mt-1">
                            <small class="{{ $cartridgeChange >= 0 ? 'text-success' : 'text-danger' }}">
                                <i class="bi bi-arrow-{{ $cartridgeChange >= 0 ? 'up' : 'down' }}"></i>
                                {{ number_format(abs($cartridgeChange), 1) }}%
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Monthly Repairs Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyChart = new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($monthlyRepairs->map(function($item) {
                return \Carbon\Carbon::create($item->year, $item->month)->format('M Y');
            })) !!},
            datasets: [{
                label: 'Заявки на ремонт',
                data: {!! json_encode($monthlyRepairs->pluck('count')) !!},
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Новые', 'В работе', 'Выполнено'],
            datasets: [{
                data: [
                    {{ $statusStats['нова'] ?? 0 }},
                    {{ $statusStats['в_роботі'] ?? 0 }},
                    {{ $statusStats['виконана'] ?? 0 }}
                ],
                backgroundColor: [
                    'rgb(245, 158, 11)',
                    'rgb(59, 130, 246)',
                    'rgb(34, 197, 94)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endpush