<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosVersion extends Model
{
    use HasFactory;

    protected $table = 'pos_versions';

    protected $fillable = [
        'version',
        'release_date',
        'notes',
        'is_latest',
        'download_url',
    ];

    protected $casts = [
        'release_date' => 'datetime',
        'is_latest' => 'boolean',
    ];
}
