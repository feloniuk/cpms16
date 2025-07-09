@extends('layouts.app')

@section('title', 'Замены картриджей')

@section('content')
<div class="row mb-4">
    <div class="col">
        <div class="stats-card p-4">
            <!-- Filters -->
            <form method="GET" action="{{ route('cartridges.index') }}" class="row g-3 align-items-end">
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
                
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Дата от</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Дата до</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                
                <div class="col-md-4">
                    <label for="search" class="form-label">Поиск</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Поиск по типу картриджа, принтеру..." value="{{ request('search') }}">
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
        <h5 class="mb-0">История замен картриджей ({{ $cartridges->total() }})</h5>
        <div>
            <a href="{{ route('cartridges.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-clockwise"></i> Обновить
            </a>
        </div>
    </div>
    
    <div class="card-body p-0">
        @if($cartridges->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Филиал</th>
                            <th>Кабинет</th>
                            <th>Принтер</th>
                            <th>Тип картриджа</th>
                            <th>Пользователь</th>
                            <th>Дата замены</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cartridges as $cartridge)
                        <tr>
                            <td><strong>#{{ $cartridge->id }}</strong></td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $cartridge->branch->name }}</span>
                            </td>
                            <td>{{ $cartridge->room_number }}</td>
                            <td>
                                <div style="max-width: 200px;">
                                    {{ Str::limit($cartridge->printer_info, 50) }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-warning">{{ $cartridge->cartridge_type }}</span>
                            </td>
                            <td>
                                @if($cartridge->username)
                                    <i class="bi bi-person"></i> @{{ $cartridge->username }}
                                @else
                                    <i class="bi bi-hash"></i> {{ $cartridge->user_telegram_id }}
                                @endif
                            </td>
                            <td>
                                <div>{{ $cartridge->replacement_date->format('d.m.Y') }}</div>
                                <small class="text-muted">{{ $cartridge->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <a href="{{ route('cartridges.show', $cartridge) }}" 
                                   class="btn btn-sm btn-outline-primary" title="Просмотр">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="card-footer bg-white">
                {{ $cartridges->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-printer fs-1 text-muted"></i>
                <h5 class="text-muted mt-3">Записи не найдены</h5>
                <p class="text-muted">Попробуйте изменить параметры поиска</p>
            </div>
        @endif
    </div>
</div>
@endsection