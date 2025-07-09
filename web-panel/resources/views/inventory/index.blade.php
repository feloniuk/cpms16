@extends('layouts.app')

@section('title', 'Управление инвентарем')

@section('content')
<div class="row mb-4">
    <div class="col">
        <div class="stats-card p-4">
            <!-- Filters -->
            <form method="GET" action="{{ route('inventory.index') }}" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label for="branch_id" class="form-label">Филиал</label>
                    <select name="branch_id" id="branch_id" class="form-select">
                        <option value="">Все филиалы</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="equipment_type" class="form-label">Тип оборудования</label>
                    <select name="equipment_type" id="equipment_type" class="form-select">
                        <option value="">Все типы</option>
                        @foreach($equipmentStats as $stat)
                            <option value="{{ $stat->equipment_type }}" {{ request('equipment_type') === $stat->equipment_type ? 'selected' : '' }}>
                                {{ $stat->equipment_type }} ({{ $stat->count }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-5">
                    <label for="search" class="form-label">Поиск</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Поиск по инв. номеру, серийному номеру, бренду..." value="{{ request('search') }}">
                </div>
                
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Найти
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col">
        <div class="d-flex justify-content-between align-items-center">
            <h2>Инвентарь ({{ $inventory->total() }})</h2>
            <div>
                <a href="{{ route('inventory.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
                   class="btn btn-outline-success me-2">
                    <i class="bi bi-download"></i> Экспорт
                </a>
                <a href="{{ route('inventory.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus"></i> Добавить оборудование
                </a>
            </div>
        </div>
    </div>
</div>

<div class="stats-card">
    <div class="card-body p-0">
        @if($inventory->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Инв. №</th>
                            <th>Филиал</th>
                            <th>Кабинет</th>
                            <th>Оборудование</th>
                            <th>Бренд/Модель</th>
                            <th>Серийный №</th>
                            <th>Дата добавления</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inventory as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->inventory_number }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $item->branch->name }}</span>
                            </td>
                            <td>{{ $item->room_number }}</td>
                            <td>
                                <div>
                                    <strong>{{ $item->equipment_type }}</strong>
                                    @if($item->notes)
                                        <br><small class="text-muted">{{ Str::limit($item->notes, 50) }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($item->brand || $item->model)
                                    <div>{{ $item->brand }} {{ $item->model }}</div>
                                @else
                                    <span class="text-muted">Не указано</span>
                                @endif
                            </td>
                            <td>
                                @if($item->serial_number)
                                    <code>{{ $item->serial_number }}</code>
                                @else
                                    <span class="text-muted">Нет</span>
                                @endif
                            </td>
                            <td>
                                <div>{{ $item->created_at->format('d.m.Y') }}</div>
                                <small class="text-muted">{{ $item->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('inventory.show', $item) }}" 
                                       class="btn btn-sm btn-outline-primary" title="Просмотр">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('inventory.edit', $item) }}" 
                                       class="btn btn-sm btn-outline-warning" title="Редактировать">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('inventory.destroy', $item) }}" 
                                          class="d-inline" onsubmit="return confirm('Удалить это оборудование?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Удалить">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="card-footer bg-white">
                {{ $inventory->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-pc-display fs-1 text-muted"></i>
                <h5 class="text-muted mt-3">Оборудование не найдено</h5>
                <p class="text-muted">Попробуйте изменить параметры поиска или добавьте новое оборудование</p>
                <a href="{{ route('inventory.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus"></i> Добавить оборудование
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mt-4">
    <div class="col-md-12">
        <div class="stats-card p-4">
            <h5 class="mb-3">Статистика по типам оборудования</h5>
            <div class="row g-3">
                @foreach($equipmentStats->take(6) as $stat)
                <div class="col-md-2">
                    <div class="text-center p-3 bg-light rounded">
                        <h4 class="text-primary">{{ $stat->count }}</h4>
                        <small class="text-muted">{{ $stat->equipment_type }}</small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection