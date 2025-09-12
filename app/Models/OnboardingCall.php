<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnboardingCall extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'user_id',
        'parent_call_id',
        'scheduled_date',
        'call_link',
        'notes',
        'status',
        'comments',
        'email_sent',
        'email_sent_at',
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'email_sent_at' => 'datetime',
        'email_sent' => 'boolean',
    ];

    const STATUS_PENDIENTE = 'pendiente';
    const STATUS_REALIZADA = 'realizada';
    const STATUS_NO_REALIZADA = 'no_realizada';
    const STATUS_REPROGRAMADA = 'reprogramada';

    public static function getStatuses()
    {
        return [
            self::STATUS_PENDIENTE => 'Pendiente',
            self::STATUS_REALIZADA => 'Realizada',
            self::STATUS_NO_REALIZADA => 'No Realizada',
        ];
    }

    public static function getAllStatuses()
    {
        return [
            self::STATUS_PENDIENTE => 'Pendiente',
            self::STATUS_REALIZADA => 'Realizada',
            self::STATUS_NO_REALIZADA => 'No Realizada',
            self::STATUS_REPROGRAMADA => 'Reprogramada',
        ];
    }

    // Relaciones
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parentCall()
    {
        return $this->belongsTo(OnboardingCall::class, 'parent_call_id');
    }

    public function childCalls()
    {
        return $this->hasMany(OnboardingCall::class, 'parent_call_id');
    }

    public function logs()
    {
        return $this->hasMany(Log::class, 'id_tabla')->where('tabla', 'onboarding_calls')->latest();
    }
}
