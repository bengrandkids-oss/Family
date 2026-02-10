<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class families extends Model
{
    //
     use HasUlids; // Commented out because App\Models\HasUlids does not exist

    protected $primaryKey = 'id';
    protected $fillable = [
        'family_name',
        'description',
        'father_id', // todo : make sure its male
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the father (head) of the family
     */
    public function father(): BelongsTo
    {
        return $this->belongsTo(family_members::class, 'father_id', 'member_id');
    }

    /**
     * Get all members of the family
     */
    public function members(): HasMany
    {
        return $this->hasMany(family_members::class, 'family_id', 'id');
    }
}