<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosVersion extends Model
{
    protected $fillable = [
        'version',
        'changelog',
        'filename',
        'is_latest',
        'release_date',
    ];

    protected $casts = [
        'release_date' => 'datetime',
        'is_latest' => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function ($posVersion) {
            if ($posVersion->is_latest) {
                // Desmarcar otras versiones como latest
                static::where('id', '!=', $posVersion->id)->update(['is_latest' => false]);
            }
        });
    }
}
