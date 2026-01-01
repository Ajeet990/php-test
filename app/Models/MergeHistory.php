<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MergeHistory extends Model
{
    protected $table = 'merge_history';
    
    public $timestamps = false;

    protected $fillable = [
        'master_contact_id',
        'merged_contact_id',
        'merge_data',
        'merged_by',
    ];

    protected $casts = [
        'merge_data' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the master contact
     */
    public function masterContact()
    {
        return $this->belongsTo(Contact::class, 'master_contact_id');
    }

    /**
     * Get the merged contact
     */
    public function mergedContact()
    {
        return $this->belongsTo(Contact::class, 'merged_contact_id');
    }
}