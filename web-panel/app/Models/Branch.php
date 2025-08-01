<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_active'];
    
    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function repairRequests()
    {
        return $this->hasMany(RepairRequest::class);
    }

    public function cartridgeReplacements()
    {
        return $this->hasMany(CartridgeReplacement::class);
    }

    public function inventory()
    {
        return $this->hasMany(RoomInventory::class);
    }
}