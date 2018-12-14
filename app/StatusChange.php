<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StatusChange extends Model
{
  protected $hidden = ['user_id', 'store_id'];

  public function user() {
    return $this->belongsTo('App\User');
  }

  public function store() {
    return $this->belongsTo('App\Store');
  }
}
