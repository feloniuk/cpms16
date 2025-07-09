@extends('layouts.app')

@section('title', 'Добавить оборудование')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="stats-card p-4">
            <div class="mb-4">
                <h4>Добавить новое оборудование</h4>
                <p class="text-muted">Заполните форму для добавления оборудования в инвентарь</p>
            </div>
            
            <form method="POST" action="{{ route('inventory.store') }}">
                @csrf
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="branch_id" class="form-label">Филиал <span class="text-danger">*</span></label>
                        <select name="branch_id" id="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                            <option value="">Выберите филиал</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="room_number" class="form-label">Номер кабинета <span class="text-danger">*</span></label>
                        <input type="text" name="room_number" id="room_number" 
                               class="form-control @error('room_number') is-invalid @enderror" 
                               value="{{ old('room_number') }}" required>
                        @error('room_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="equipment_type" class="form-label">Тип оборудования <span class="text-danger">*</span></label>
                        <input type="text" name="equipment_type" id="equipment_type" 
                               class="form-control @error('equipment_type') is-invalid @enderror" 
                               value="{{ old('equipment_type') }}" 
                               placeholder="Например: Компьютер, Принтер, Монитор" required>
                        @error('equipment_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="inventory_number" class="form-label">Инвентарный номер <span class="text-danger">*</span></label>
                        <input type="text" name="inventory_number" id="inventory_number" 
                               class="form-control @error('inventory_number') is-invalid @enderror" 
                               value="{{ old('inventory_number') }}" required>
                        @error('inventory_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="brand" class="form-label">Бренд</label>
                        <input type="text" name="brand" id="brand" 
                               class="form-control @error('brand') is-invalid @enderror" 
                               value="{{ old('brand') }}" placeholder="HP, Dell, Lenovo...">
                        @error('brand')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="model" class="form-label">Модель</label>
                        <input type="text" name="model" id="model" 
                               class="form-control @error('model') is-invalid @enderror" 
                               value="{{ old('model') }}">
                        @error('model')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-12">
                        <label for="serial_number" class="form-label">Серийный номер</label>
                        <input type="text" name="serial_number" id="serial_number" 
                               class="form-control @error('serial_number') is-invalid @enderror" 
                               value="{{ old('serial_number') }}">
                        @error('serial_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-12">
                        <label for="notes" class="form-label">Заметки</label>
                        <textarea name="notes" id="notes" rows="3" 
                                  class="form-control @error('notes') is-invalid @enderror" 
                                  placeholder="Дополнительная информация об оборудовании">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Отмена
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection