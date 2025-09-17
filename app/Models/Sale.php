<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'user_id',
        'llamada_id',
        'nombre_cliente',
        'apellido_cliente',
        'email_cliente',
        'telefono_cliente',
        'identificacion_personal',
        'domicilio',
        'metodo_pago',
        'comprobante_pago_path',
        'tipo_acuerdo',
        'comentarios',
        'contract_template_id',
        'contract_approved',
        'contract_data',
        'contract_token',
        'contract_signed_date',
    ];

    protected $casts = [
        'contract_data' => 'array',
        'contract_approved' => 'boolean',
        'contract_signed_date' => 'datetime',
    ];

    // Relaciones

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function llamada()
    {
        return $this->belongsTo(Llamada::class);
    }

    public function contractTemplate()
    {
        return $this->belongsTo(ContractTemplate::class);
    }
}
