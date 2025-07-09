@extends('layouts.app')

@section('title', 'Заявки на ремонт')

@section('content')
<div class="row mb-4">
    <div class="col">
        <div class="stats-card p-4">
            <!-- Filters -->
            <form method="GET" action="{{ route('repairs.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="status" class="form-label">Статус</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Все статусы</option>
                        <option value="нова" {{ request('status') === 'нова' ? 'selected' : '' }}>Новые</option>
                        <option value="в_роботі" {{ request('status') === 'в_роботі' ? 'selected' : '' }}>В работе</option>
                        <option value="виконана" {{ request('status') === 'виконана' ? 'selected' : '' }}>Выполнено</option>
                    </select>
                </div>
                
                <div class="col-md-3">
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
                
                <div class="col-md-4">
                    <label for="search" class="form-label">Поиск</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Поиск по описанию, кабинету..." value="{{ request('search') }}">
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

<div class="stats-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Список заявок ({{ $repairs->total() }})</h5>
        <div>
            <a href="{{ route('repairs.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-clockwise"></i> Обновить
            </a>
        </div>
    </div>
    
    <div class="card-body p-0">
        @if($repairs->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Филиал</th>
                            <th>Кабинет</th>
                            <th>Описание</th>
                            <th>Пользователь</th>
                            <th>Статус</th>
                            <th>Дата</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($repairs as $repair)
                        <tr>
                            <td><strong>#{{ $repair->id }}</strong></td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $repair->branch->name }}</span>
                            </td>
                            <td>{{ $repair->room_number }}</td>
                            <td>
                                <div style="max-width: 300px;">
                                    {{ Str::limit($repair->description, 100) }}
                                </div>
                            </td>
                            <td>
                                @if($repair->username)
                                    <i class="bi bi-person"></i> @{{ $repair->username }}
                                @else
                                    <i class="bi bi-hash"></i> {{ $repair->user_telegram_id }}
                                @endif
                                @if($repair->phone)
                                    <br><small class="text-muted">
                                        <i class="bi bi-telephone"></i> {{ $repair->phone }}
                                    </small>
                                @endif
                            </td>
                            <td>{!! $repair->status_badge !!}</td>
                            <td>
                                <div>{{ $repair->created_at->format('d.m.Y') }}</div>
                                <small class="text-muted">{{ $repair->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('repairs.show', $repair) }}" 
                                       class="btn btn-sm btn-outline-primary" title="Просмотр">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    
                                    @if($repair->status !== 'виконана')
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-success dropdown-toggle" 
                                                data-bs-toggle="dropdown" title="Изменить статус">
                                            <i class="bi bi-gear"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            @if($repair->status !== 'в_роботі')
                                            <li>
                                                <form method="POST" action="{{ route('repairs.update', $repair) }}" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="в_роботі">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-gear text-warning"></i> В работу
                                                    </button>
                                                </form>
                                            </li>
                                            @endif
                                            <li>
                                                <form method="POST" action="{{ route('repairs.update', $repair) }}" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="виконана">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-check-circle text-success"></i> Выполнено
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="card-footer bg-white">
                {{ $repairs->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted"></i>
                <h5 class="text-muted mt-3">Заявки не найдены</h5>
                <p class="text-muted">Попробуйте изменить параметры поиска</p>
            </div>
        @endif
    </div>
</div>
@endsection