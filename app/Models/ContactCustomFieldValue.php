<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactCustomFieldValue extends Model
{
    protected $fillable = [
        'contact_id',
        'custom_field_id',
        'field_value',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the contact that owns this value
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Get the custom field definition
     */
    public function customField()
    {
        return $this->belongsTo(CustomField::class);
    }
}