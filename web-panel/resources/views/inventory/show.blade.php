@extends('layouts.app')

@section('title', 'Оборудование #' . $inventory->inventory_number)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="stats-card p-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h4>{{ $inventory->equipment_type }}</h4>
                    <p class="text-muted mb-0">Инвентарный номер: <strong>{{ $inventory->inventory_number }}</strong></p>
                </div>
                <div>
                    <a href="{{ route('inventory.edit', $inventory) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Редактировать
                    </a>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Филиал</h6>
                    <p class="mb-0">{{ $inventory->branch->name }}</p>
                </div>
                
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Номер кабинета</h6>
                    <p class="mb-0">{{ $inventory->room_number }}</p>
                </div>
                
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Бренд</h6>
                    <p class="mb-0">{{ $inventory->brand ?: 'Не указан' }}</p>
                </div>
                
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Модель</h6>
                    <p class="mb-0">{{ $inventory->model ?: 'Не указана' }}</p>
                </div>
                
                @if($inventory->serial_number)
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Серийный номер</h6>
                    <p class="mb-0"><code>{{ $inventory->serial_number }}</code></p>
                </div>
                @endif
                
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Дата добавления</h6>
                    <p class="mb-0">{{ $inventory->created_at->format('d.m.Y H:i:s') }}</p>
                </div>
                
                @if($inventory->notes)
                <div class="col-12">
                    <h6 class="text-muted mb-2">Заметки</h6>
                    <div class="bg-light p-3 rounded">
                        <p class="mb-0">{{ $inventory->notes }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        @if($cartridgeReplacements && $cartridgeReplacements->count() > 0)
        <div class="stats-card p-4 mt-4">
            <h5 class="mb-3">История замен картриджей</h5>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Дата</th>
                            <th>Тип картриджа</th>
                            <th>Пользователь</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cartridgeReplacements as $replacement)
                        <tr>
                            <td>{{ $replacement->replacement_date->format('d.m.Y') }}</td>
                            <td><span class="badge bg-warning">{{ $replacement->cartridge_type }}</span></td>
                            <td>
                                @if($replacement->username)
                                    @{{ $replacement->username }}
                                @else
                                    ID: {{ $replacement->user_telegram_id }}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
    
    <div class="col-lg-4">
        <div class="stats-card p-4">
            <h5 class="mb-3">Действия</h5>
            
            <div class="d-grid gap-2">
                <a href="{{ route('inventory.edit', $inventory) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Редактировать
                </a>
                
                <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Назад к списку
                </a>
                
                <form method="POST" action="{{ route('inventory.destroy', $inventory) }}" 
                      onsubmit="return confirm('Вы уверены, что хотите удалить это оборудование?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="bi bi-trash"></i> Удалить
                    </button>
                </form>
            </div>
        </div>
        
        <!-- QR Code for inventory number -->
        <div class="stats-card p-4 mt-4">
            <h5 class="mb-3">QR код</h5>
            <div class="text-center">
                <div id="qrcode"></div>
                <small class="text-muted mt-2 d-block">{{ $inventory->inventory_number }}</small>
            </div>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
    // Generate QR code for inventory number
    QRCode.toCanvas(document.getElementById('qrcode'), '{{ $inventory->inventory_number }}', {
        width: 200,
        height: 200,
        margin: 2
    });
</script>
@endpush