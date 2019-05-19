<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    protected $fillable = [
        'type',
        'start',
        'end',
    ];

    protected $dates = [
        'start',
        'end',
    ];

    /**
     *  Relationships
     */

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
