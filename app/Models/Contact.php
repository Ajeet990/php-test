<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'gender',
        'profile_image',
        'additional_file',
        'is_merged',
        'merged_into',
    ];

    protected $casts = [
        'is_merged' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function emails()
    {
        return $this->hasMany(ContactEmail::class);
    }

    public function primaryEmail()
    {
        return $this->hasOne(ContactEmail::class)->where('is_primary', true);
    }

    public function phones()
    {
        return $this->hasMany(ContactPhone::class);
    }

    public function primaryPhone()
    {
        return $this->hasOne(ContactPhone::class)->where('is_primary', true);
    }

    public function customFieldValues()
    {
        return $this->hasMany(ContactCustomFieldValue::class);
    }

    public function mergedIntoContact()
    {
        return $this->belongsTo(Contact::class, 'merged_into');
    }

    public function mergedContacts()
    {
        return $this->hasMany(Contact::class, 'merged_into');
    }

    public function masterMergeHistory()
    {
        return $this->hasMany(MergeHistory::class, 'master_contact_id');
    }

    public function mergedHistory()
    {
        return $this->hasOne(MergeHistory::class, 'merged_contact_id');
    }

    public function scopeNotMerged($query)
    {
        return $query->where('is_merged', false);
    }

    public function scopeMerged($query)
    {
        return $query->where('is_merged', true);
    }
}

