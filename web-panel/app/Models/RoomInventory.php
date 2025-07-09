<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomInventory extends Model
{
    use HasFactory;

    protected $table = 'room_inventory';

    protected $fillable = [
        'admin_telegram_id',
        'branch_id',
        'room_number',
        'template_id',
        'equipment_type',
        'brand',
        'model',
        'serial_number',
        'inventory_number',
        'notes'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function template()
    {
        return $this->belongsTo(InventoryTemplate::class, 'template_id');
    }
}