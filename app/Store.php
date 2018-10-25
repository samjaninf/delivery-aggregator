<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
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
}
