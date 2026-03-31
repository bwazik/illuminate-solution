<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    protected $fillable = ['remote_id', 'location', 'metadata', 'tags', 'reports', 'occurred_at'];

    protected $casts = [
        'metadata' => 'array',
        'tags' => 'array',
        'reports' => 'array',
    ];

    public function getCodeAttribute(): string
    {
        $metadata = is_string($this->metadata)
            ? json_decode($this->metadata, true)
            : $this->metadata;

        return $metadata['incident']['code'] ?? '';
    }
}
