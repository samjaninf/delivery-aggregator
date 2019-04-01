<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusChange extends Model
{
    protected $hidden = ['user_id', 'store_id'];

    /*******************
     *  RELATIONSHIPS  *
     *******************/

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\User');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo('App\Store');
    }
}
