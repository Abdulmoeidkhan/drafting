<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = [
        'name',
        'short_code',
        'captain_name',
        'email',
        'max_players',
        'logo',
    ];

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }
}
