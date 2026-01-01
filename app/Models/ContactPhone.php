<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactPhone extends Model
{
    protected $fillable = [
        'contact_id',
        'phone',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}