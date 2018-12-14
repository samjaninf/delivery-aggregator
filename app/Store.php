<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Store extends Model
{
    public static function boot() {
        parent::boot();

        static::created(function($store) {
            $store->fb_create_group();
        });
        
        static::deleting(function($store) {
            $store->fb_delete_group();
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
        return $this->belongsToMany('App\User')->withPivot('fb_registered');
    }

    public function statusChanges()
    {
        return $this->hasMany('App\StatusChange');
    }

    /************************
     *  FIREBASE FUNCTIONS  *
     ************************/

    public function fb_create_group($user = null)
    {
        $user = $user ?? auth()->user();

        $deviceId = $user->fb_device_id;
        if (!$deviceId) {
            Log::warning("FB: Active user doesn't have a valid fb_device_id, cannot create Firebase group!");
            return;
        }

        $response = fb_curl_post([
            "operation" => "create",
            "notification_key_name" => $this->code,
            "registration_ids" => [ $deviceId ]
        ]);

        if($response && !isset($response->error)) {
            Log::info("FB: Firebase group creation for store {$this->code} completed with response " . print_r($response, true));
            $this->fb_notification_key = $response->notification_key;
            $this->save();
        } else {
            Log::error("FB: Firebase group creation for store {$this->code} failed with response " . print_r($response, true));
        }
    }

    public function fb_delete_group()
    {
        if (!$this->fb_notification_key) {
            Log::warning("FB: Store {$this->code} doesn't have a valid fb_notification_key, ignoring request to delete group from Firebase!");
            return;
        }

        // Deleting all the registered devices
        $deviceIds = $this->users->pluck('fb_device_id')->filter(function ($id) { return isset($id); });

        $response = fb_curl_post([
            "operation" => "remove",
            "notification_key_name" => $this->code,
            "notification_key" => $this->fb_notification_key,
            "registration_ids" => $deviceIds
        ]);
        
        if ($response && !isset($response->error)) {
            Log::info("FB: Firebase group deletion for store {$this->code} completed with response " . print_r($response, true));
        } else {
            Log::error("FB: Firebase group deletion for store {$this->code} failed with response " . print_r($response, true));
        }
    }
}
