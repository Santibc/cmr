<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'html_content',
        'dynamic_fields'
    ];

    protected $casts = [
        'dynamic_fields' => 'array'
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
