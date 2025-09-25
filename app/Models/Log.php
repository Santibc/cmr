<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Log extends Model
{
    use HasFactory;

    protected $table = 'logs';

    protected $fillable = [
        'id_tabla',
        'tabla',
        'detalle',
        'archivo_soporte',
        'tipo_log',
        'valor_viejo',
        'valor_nuevo',
        'id_usuario',
        'estado',
        'id_archivo',
    ];

    /**
     * Relación con el modelo User
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
