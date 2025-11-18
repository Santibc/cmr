<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Form extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'slug',
        'status',
        'module',
        'trigger_event',
        'settings',
        'user_id',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    public static function getStatuses()
    {
        return [
            self::STATUS_DRAFT => 'Borrador',
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_INACTIVE => 'Inactivo',
        ];
    }

    // Relationships
    public function fields()
    {
        return $this->hasMany(FormField::class)->orderBy('order');
    }

    public function submissions()
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Logs using the manual polymorphic pattern from the project
    public function logs()
    {
        return $this->hasMany(Log::class, 'id_tabla')
                    ->where('tabla', 'forms')
                    ->latest();
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isDraft()
    {
        return $this->status === self::STATUS_DRAFT;
    }
}
