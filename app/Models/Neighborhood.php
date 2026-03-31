<?php

namespace App\Models;

use App\Relations\DonutRelation;
use Illuminate\Database\Eloquent\Model;

class Neighborhood extends Model
{
    protected $fillable = ['remote_id', 'name', 'boundary', 'properties'];

    protected $casts = [
        'properties' => 'array',
    ];

    public function incidents(): DonutRelation
    {
        return new DonutRelation($this);
    }
}
