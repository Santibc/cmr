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

    /**
     * Get the dynamic fields attribute and ensure it's always an array
     */
    public function getDynamicFieldsAttribute($value)
    {
        // If it's already an array, return it
        if (is_array($value)) {
            return $value;
        }

        // If it's null or empty, return empty array
        if (empty($value)) {
            return [];
        }

        // Try to decode JSON
        $decoded = json_decode($value, true);

        // If JSON decode worked, return it
        if (is_array($decoded)) {
            return $decoded;
        }

        // If value is a string that looks like it has double-escaped quotes, try to fix it
        if (is_string($value) && strpos($value, '\\"') !== false) {
            // Remove outer quotes if present
            $cleaned = trim($value, '"');
            // Replace escaped quotes
            $cleaned = str_replace('\\"', '"', $cleaned);
            $decoded = json_decode($cleaned, true);

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // Last resort: return empty array to prevent errors
        return [];
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
