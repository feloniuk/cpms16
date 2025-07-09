<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_telegram_id',
        'username',
        'branch_id',
        'room_number',
        'description',
        'phone',
        'status'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'нова' => '<span class="badge bg-primary">Нова</span>',
            'в_роботі' => '<span class="badge bg-warning">В роботі</span>',
            'виконана' => '<span class="badge bg-success">Виконана</span>',
            default => '<span class="badge bg-secondary">Невідомо</span>'
        };
    }
}
