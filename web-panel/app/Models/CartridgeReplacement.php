<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartridgeReplacement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_telegram_id',
        'username',
        'branch_id',
        'room_number',
        'printer_inventory_id',
        'printer_info',
        'cartridge_type',
        'replacement_date',
        'notes'
    ];

    protected $casts = [
        'replacement_date' => 'date',
        'created_at' => 'datetime'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function printer()
    {
        return $this->belongsTo(RoomInventory::class, 'printer_inventory_id');
    }
}
