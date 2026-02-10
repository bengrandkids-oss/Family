<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class relations extends Model
{
    //
    use HasUlids;
        protected $fillable = [
            'primary',
            'secondary',
            'relationship_type'
        ];

        protected $table = 'relations';
        protected $primaryKey = 'id';

        public function primaryMember()
        {
            return $this->belongsTo(family_members::class, 'primary', 'member_id');
        }

        public function secondaryMember()
        {
            return $this->belongsTo(family_members::class, 'secondary', 'member_id');
        }

        
}
