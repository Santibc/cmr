<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'lead_id',
        'user_id',
        'submission_data',
        'ip_address',
        'user_agent',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'submission_data' => 'array',
        'submitted_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_APPROVED => 'Aprobado',
            self::STATUS_REJECTED => 'Rechazado',
        ];
    }

    // Relationships
    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Logs using the manual polymorphic pattern from the project
    public function logs()
    {
        return $this->hasMany(Log::class, 'id_tabla')
                    ->where('tabla', 'form_submissions')
                    ->latest();
    }

    // Helper methods
    public function getFieldValue($fieldName, $default = null)
    {
        return $this->submission_data[$fieldName] ?? $default;
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
