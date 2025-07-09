<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'equipment_type',
        'brand',
        'model',
        'requires_serial',
        'requires_inventory'
    ];

    protected $casts = [
        'requires_serial' => 'boolean',
        'requires_inventory' => 'boolean'
    ];

    public function inventoryItems()
    {
        return $this->hasMany(RoomInventory::class, 'template_id');
    }
}