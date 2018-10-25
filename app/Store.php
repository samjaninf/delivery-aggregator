<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    public static function boot() {
        parent::boot();

        static::deleting(function($store) {
            $store->users()->detach();
        });
    }

    protected $fillable = [
        'name',
        'code',
        'url',
        'consumer_key',
        'consumer_secret'
    ];

    public static function findByCode(string $code) {
        return Store::where('code', $code)->first();
    }

    public function users() {
        return $this->belongsToMany('App\User');
    }
}
