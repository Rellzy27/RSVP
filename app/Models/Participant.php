<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Participant extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'sudah_komuni' => 'boolean',
    ];

    /**
     * Dapatkan pendaftaran (invoice) induk.
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}