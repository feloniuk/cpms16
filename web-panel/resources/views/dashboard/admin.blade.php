@extends('layouts.app')

@section('title', 'Дашборд администратора')

@section('content')
<div class="row g-4">
    <!-- Statistics Cards -->
    <div class="col-md-3">
        <div class="stats-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="text-muted mb-2">Всего заявок</h6>
                    <h3 class="mb-0">{{ $repairStats['total'] }}</h3>
                </div>
                <div class="bg-primary bg-opacity-10 p-3 rounded">
                    <i class="bi bi-tools text-primary fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="text-muted mb-2">Новые заявки</h6>
                    <h3 class="mb-0 text-warning">{{ $repairStats['new'] }}</h3>
                </div>
                <div class="bg-warning bg-opacity-10 p-3 rounded">
                    <i class="bi bi-exclamation-triangle text-warning fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="text-muted mb-2">В работе</h6>
                    <h3 class="mb-0 text-info">{{ $repairStats['in_progress'] }}</h3>
                </div>
                <div class="bg-info bg-opacity-10 p-3 rounded">
                    <i class="bi bi-gear text-info fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="text-muted mb-2">Выполнено</h6>
                    <h3 class="mb-0 text-success">{{ $repairStats['completed'] }}</h3>
                </div>
                <div class="bg-success bg-opacity-10 p-3 rounded">
                    <i class="bi bi-check-circle text-success fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <!-- Recent Repairs -->
    <div class="col-lg-8">
        <div class="stats-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">Последние заявки на ремонт</h5>
                <a href="{{ route('repairs.index') }}" class="btn btn-sm btn-outline-primary">
                    Все заявки <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            
            @if($recentRepairs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Филиал</th>
                                <th>Кабинет</th>
                                <th>Статус</th>
                                <th>Дата</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentRepairs as $repair)
                            <tr>
                                <td>{{ $repair->id }}</td>
                                <td>{{ $repair->branch->name }}</td>
                                <td>{{ $repair->room_number }}</td>
                                <td>{!! $repair->status_badge !!}</td>
                                <td>{{ $repair->created_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('repairs.show', $repair) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="text-muted mt-2">Заявок пока нет</p>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Branch Statistics -->
    <div class="col-lg-4">
        <div class="stats-card p-4 h-100">
            <h5 class="card-title mb-3">Статистика по филиалам</h5>
            
            @foreach($branchStats->take(5) as $branch)
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="mb-1">{{ $branch->name }}</h6>
                    <small class="text-muted">{{ $branch->repair_requests_count }} заявок</small>
                </div>
                <div class="text-end">
                    <span class="badge bg-light text-dark">{{ $branch->cartridge_replacements_count }} картриджей</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <!-- Additional Stats -->
    <div class="col-md-6">
        <div class="stats-card p-4">
            <h5 class="card-title mb-3">Замены картриджей</h5>
            <div class="d-flex align-items-center">
                <div class="bg-warning bg-opacity-10 p-3 rounded me-3">
                    <i class="bi bi-printer text-warning fs-4"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $cartridgeCount }}</h3>
                    <small class="text-muted">За последний месяц</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="stats-card p-4">
            <h5 class="card-title mb-3">Инвентарь</h5>
            <div class="d-flex align-items-center">
                <div class="bg-info bg-opacity-10 p-3 rounded me-3">
                    <i class="bi bi-pc-display text-info fs-4"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $inventoryCount }}</h3>
                    <small class="text-muted">Единиц оборудования</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection