<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'label',
        'field_type',
        'field_name',
        'placeholder',
        'default_value',
        'options',
        'validations',
        'order',
        'is_required',
        'help_text',
    ];

    protected $casts = [
        'options' => 'array',
        'validations' => 'array',
        'is_required' => 'boolean',
    ];

    // Field type constants
    const TYPE_TEXT = 'text';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_EMAIL = 'email';
    const TYPE_NUMBER = 'number';
    const TYPE_DATE = 'date';
    const TYPE_SELECT = 'select';
    const TYPE_RADIO = 'radio';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_SCALE = 'scale';
    const TYPE_RATING = 'rating';

    public static function getFieldTypes()
    {
        return [
            self::TYPE_TEXT => 'Texto corto',
            self::TYPE_TEXTAREA => 'Texto largo',
            self::TYPE_EMAIL => 'Email',
            self::TYPE_NUMBER => 'Número',
            self::TYPE_DATE => 'Fecha',
            self::TYPE_SELECT => 'Lista desplegable',
            self::TYPE_RADIO => 'Selección única',
            self::TYPE_CHECKBOX => 'Selección múltiple',
            self::TYPE_SCALE => 'Escala numérica',
            self::TYPE_RATING => 'Calificación por estrellas',
        ];
    }

    // Relationships
    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    // Helper methods
    public function hasOptions()
    {
        return in_array($this->field_type, [
            self::TYPE_SELECT,
            self::TYPE_RADIO,
            self::TYPE_CHECKBOX,
        ]);
    }

    public function isScale()
    {
        return $this->field_type === self::TYPE_SCALE;
    }

    public function isRating()
    {
        return $this->field_type === self::TYPE_RATING;
    }
}
