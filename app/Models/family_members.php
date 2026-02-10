<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class family_members extends Model
{
    //
    use HasUlids;

    protected $primaryKey = 'member_id';

    protected $fillable = [
        'first_name',
        'last_name',
        'family_name',
        'gender',
        'date_of_birth',
        'date_of_death',
        'photo'
    ];
};
