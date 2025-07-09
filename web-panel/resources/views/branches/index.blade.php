@extends('layouts.app')

@section('title', 'Управление филиалами')

@section('content')
<div class="row mb-4">
    <div class="col">
        <div class="d-flex justify-content-between align-items-center">
            <h2>Филиалы</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBranchModal">
                <i class="bi bi-plus"></i> Добавить филиал
            </button>
        </div>
    </div>
</div>

<div class="row g-4">
    @foreach($branches as $branch)
    <div class="col-lg-6">
        <div class="stats-card p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="mb-1">{{ $branch->name }}</h5>
                    <span class="badge {{ $branch->is_active ? 'bg-success' : 'bg-secondary' }}">
                        {{ $branch->is_active ? 'Активна' : 'Неактивна' }}
                    </span>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" 
                            data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="{{ route('branches.show', $branch) }}">
                                <i class="bi bi-eye"></i> Просмотр
                            </a>
                        </li>
                        <li>
                            <button class="dropdown-item" onclick="editBranch({{ $branch->id }}, '{{ $branch->name }}', {{ $branch->is_active ? 'true' : 'false' }})">
                                <i class="bi bi-pencil"></i> Редактировать
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="row g-3">
                <div class="col-4">
                    <div class="text-center">
                        <h4 class="text-primary mb-1">{{ $branch->repair_requests_count }}</h4>
                        <small class="text-muted">Заявки</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="text-center">
                        <h4 class="text-warning mb-1">{{ $branch->cartridge_replacements_count }}</h4>
                        <small class="text-muted">Картриджи</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="text-center">
                        <h4 class="text-info mb-1">{{ $branch->inventory_count }}</h4>
                        <small class="text-muted">Инвентарь</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Add Branch Modal -->
<div class="modal fade" id="addBranchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('branches.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Добавить филиал</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Название филиала</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Создать</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Branch Modal -->
<div class="modal fade" id="editBranchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editBranchForm">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">Редактировать филиал</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Название филиала</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                        <label class="form-check-label" for="edit_is_active">
                            Активна
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editBranch(id, name, isActive) {
    document.getElementById('editBranchForm').action = `/web-panel/branches/${id}`;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_is_active').checked = isActive;
    
    const editModal = new bootstrap.Modal(document.getElementById('editBranchModal'));
    editModal.show();
}
</script>
@endpush