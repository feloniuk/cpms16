@extends('layouts.app')

@section('title', 'Замена картриджа #' . $cartridge->id)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="stats-card p-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h4>Замена картриджа #{{ $cartridge->id }}</h4>
                    <p class="text-muted mb-0">Запрос создан {{ $cartridge->created_at->format('d.m.Y в H:i') }}</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Филиал</h6>
                    <p class="mb-0">{{ $cartridge->branch->name }}</p>
                </div>
                
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Номер кабинета</h6>
                    <p class="mb-0">{{ $cartridge->room_number }}</p>
                </div>
                
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Пользователь</h6>
                    <p class="mb-0">
                        @if($cartridge->username)
                            <i class="bi bi-person"></i> @{{ $cartridge->username }}
                        @else
                            <i class="bi bi-hash"></i> ID: {{ $cartridge->user_telegram_id }}
                        @endif
                    </p>
                </div>
                
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Дата замены</h6>
                    <p class="mb-0">{{ $cartridge->replacement_date->format('d.m.Y') }}</p>
                </div>
                
                <div class="col-12">
                    <h6 class="text-muted mb-2">Информация о принтере</h6>
                    <div class="bg-light p-3 rounded">
                        <p class="mb-0">{{ $cartridge->printer_info }}</p>
                    </div>
                </div>
                
                <div class="col-12">
                    <h6 class="text-muted mb-2">Тип картриджа</h6>
                    <span class="badge bg-warning fs-6">{{ $cartridge->cartridge_type }}</span>
                </div>
                
                @if($cartridge->notes)
                <div class="col-12">
                    <h6 class="text-muted mb-2">Заметки</h6>
                    <div class="bg-light p-3 rounded">
                        <p class="mb-0">{{ $cartridge->notes }}</p>
                    </div>
                </div>
                @endif
                
                @if($cartridge->printer)
                <div class="col-12">
                    <h6 class="text-muted mb-2">Связанный инвентарь</h6>
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">{{ $cartridge->printer->equipment_type }}</h6>
                            <p class="card-text">
                                <strong>Бренд:</strong> {{ $cartridge->printer->brand }}<br>
                                <strong>Модель:</strong> {{ $cartridge->printer->model }}<br>
                                <strong>Инв. номер:</strong> {{ $cartridge->printer->inventory_number }}
                                @if($cartridge->printer->serial_number)
                                    <br><strong>Серийный номер:</strong> {{ $cartridge->printer->serial_number }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="stats-card p-4">
            <h5 class="mb-3">Действия</h5>
            
            <div class="d-grid gap-2">
                <a href="{{ route('cartridges.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Назад к списку
                </a>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="stats-card p-4 mt-4">
            <h5 class="mb-3">Статистика</h5>
            
            <div class="mb-3">
                <h6 class="text-muted mb-1">Дата создания</h6>
                <p class="mb-0">{{ $cartridge->created_at->format('d.m.Y H:i:s') }}</p>
            </div>
            
            <div class="mb-3">
                <h6 class="text-muted mb-1">Время обработки</h6>
                <p class="mb-0">{{ $cartridge->created_at->diffForHumans() }}</p>
            </div>
        </div>
    </div>
</div>
@endsection