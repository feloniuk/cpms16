@extends('layouts.app')

@section('title', 'Заявка #' . $repair->id)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="stats-card p-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h4>Заявка на ремонт #{{ $repair->id }}</h4>
                    <p class="text-muted mb-0">Создана {{ $repair->created_at->format('d.m.Y в H:i') }}</p>
                </div>
                <div>
                    {!! $repair->status_badge !!}
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Филиал</h6>
                    <p class="mb-0">{{ $repair->branch->name }}</p>
                </div>
                
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Номер кабинета</h6>
                    <p class="mb-0">{{ $repair->room_number }}</p>
                </div>
                
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Пользователь</h6>
                    <p class="mb-0">
                        @if($repair->username)
                            <i class="bi bi-person"></i> @{{ $repair->username }}
                        @else
                            <i class="bi bi-hash"></i> ID: {{ $repair->user_telegram_id }}
                        @endif
                    </p>
                </div>
                
                @if($repair->phone)
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Телефон</h6>
                    <p class="mb-0">
                        <i class="bi bi-telephone"></i> 
                        <a href="tel:{{ $repair->phone }}">{{ $repair->phone }}</a>
                    </p>
                </div>
                @endif
                
                <div class="col-12">
                    <h6 class="text-muted mb-2">Описание проблемы</h6>
                    <div class="bg-light p-3 rounded">
                        <p class="mb-0">{{ $repair->description }}</p>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Дата создания</h6>
                    <p class="mb-0">{{ $repair->created_at->format('d.m.Y H:i:s') }}</p>
                </div>
                
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Последнее обновление</h6>
                    <p class="mb-0">{{ $repair->updated_at->format('d.m.Y H:i:s') }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="stats-card p-4">
            <h5 class="mb-3">Действия</h5>
            
            @if($repair->status !== 'виконана')
                <div class="d-grid gap-2">
                    @if($repair->status === 'нова')
                    <form method="POST" action="{{ route('repairs.update', $repair) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="в_роботі">
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="bi bi-gear"></i> Взять в работу
                        </button>
                    </form>
                    @endif
                    
                    <form method="POST" action="{{ route('repairs.update', $repair) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="виконана">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle"></i> Отметить выполненным
                        </button>
                    </form>
                </div>
                
                <hr>
            @endif
            
            <div class="d-grid gap-2">
                <a href="{{ route('repairs.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Назад к списку
                </a>
                
                @if($repair->phone)
                <a href="tel:{{ $repair->phone }}" class="btn btn-outline-primary">
                    <i class="bi bi-telephone"></i> Позвонить
                </a>
                @endif
            </div>
        </div>
        
        <!-- Timeline -->
        <div class="stats-card p-4 mt-4">
            <h5 class="mb-3">История изменений</h5>
            
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-marker bg-primary"></div>
                    <div class="timeline-content">
                        <h6 class="mb-1">Заявка создана</h6>
                        <small class="text-muted">{{ $repair->created_at->format('d.m.Y H:i') }}</small>
                    </div>
                </div>
                
                @if($repair->status !== 'нова')
                <div class="timeline-item">
                    <div class="timeline-marker bg-warning"></div>
                    <div class="timeline-content">
                        <h6 class="mb-1">Принята в работу</h6>
                        <small class="text-muted">{{ $repair->updated_at->format('d.m.Y H:i') }}</small>
                    </div>
                </div>
                @endif
                
                @if($repair->status === 'виконана')
                <div class="timeline-item">
                    <div class="timeline-marker bg-success"></div>
                    <div class="timeline-content">
                        <h6 class="mb-1">Заявка выполнена</h6>
                        <small class="text-muted">{{ $repair->updated_at->format('d.m.Y H:i') }}</small>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -37px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
}

.timeline-content {
    padding-left: 15px;
}
</style>
@endpush