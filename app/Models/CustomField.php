<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    protected $fillable = [
        'field_name',
        'field_label',
        'field_type',
        'field_options',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'field_options' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function values()
    {
        return $this->hasMany(ContactCustomFieldValue::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
