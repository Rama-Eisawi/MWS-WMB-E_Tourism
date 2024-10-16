<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;
    protected $fillable = ['type', 'name', 'description'];
    public function tours()
    {
        return $this->hasMany(Tour::class);
    }
}
