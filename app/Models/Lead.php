<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nombre',
        'email',
        'telefono',
        'instagram_user',
        'passed_to_closer',
        'passed_to_closer_at',
        'passed_by_user_id',
    ];

    // Un lead puede tener varias llamadas
    public function llamadas()
    {
        return $this->hasMany(Llamada::class);
    }

    // Un lead pertenece (opcionalmente) a un usuario (closer)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function closer()
{
    return $this->belongsTo(User::class, 'user_id');
}
    public function pipelineStatus()
    {
        return $this->belongsTo(PipelineStatus::class);
    }
    public function sale()
{
    return $this->hasOne(Sale::class);
}
public function logs()
{
    return $this->hasMany(Log::class, 'id_tabla')->where('tabla', 'leads')->latest();
}
public function onboardingCalls()
{
    return $this->hasMany(OnboardingCall::class);
}
public function notes()
{
    return $this->hasMany(LeadNote::class)->latest();
}
public function traigeCalls()
{
    return $this->hasMany(TraigeCall::class);
}
public function passedByUser()
{
    return $this->belongsTo(User::class, 'passed_by_user_id');
}
}
