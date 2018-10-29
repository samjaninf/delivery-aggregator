<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Log;


class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    public static function boot() {
        parent::boot();

        static::updating(function($user) {
            if ($user->getOriginal('fb_device_id') != $user->fb_device_id) {
                // Update the fb registration groups with the new device id
                $user->fb_update_groups();
            }
        });

        static::deleting(function($user) {
            $user->stores()->detach();
        });
    }

    protected $fillable = [
        'name', 'email', 'password', 'is_admin'
    ];

    protected $hidden = [
        'password'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function stores()
    {
        return $this->belongsToMany('App\Store')->withPivot('fb_registered');
    }

    /************************
     *  FIREBASE FUNCTIONS  *
     ************************/

    private function fb_update_groups() {
        $old_device_id = $user->getOriginal('fb_device_id');
        if (isset($old_device_id)) {
            $this->fb_unsubscribe_from_groups($old_device_id);
        }

        if ($this->fb_device_id)
            $this->fb_subscribe_to_groups($this->fb_device_id);
    }

    
    private function fb_unsubscribe_from_groups($device_id)
    {
        foreach($this->stores as $store)
        {
            $this->fb_unsubscribe_from_group($store, $device_id);
        }
    }
    
    private function fb_subscribe_to_groups($device_id)
    {
        foreach($this->stores as $store)
        {
            $this->fb_subscribe_to_group($store, $device_id);
        }
    }

    private function fb_unsubscribe_from_group($store, $device_id = null)
    {
        if (!isset($device_id))
            $device_id = $this->fb_device_id;

        $s = $this->stores->firstWhere('id', $store->id);
        // User not registered to provided store
        if (!$s || !$s->pivot->fb_registered)
            return;


        $response = fb_curl_post([
            "operation" => "remove",
            "notification_key_name" => $store->code,
            "notification_key" => $store->fb_registration_key,
            "registration_ids" => [ $device_id ] 
        ]);

        if($response) {
            Log::info("FB: Firebase user unsubscription for user {$this->email} from store {$this->code} completed with response " . print_r($response, true));
            $this->stores()->updateExistingPivot($store->id, [ 'fb_registered' => false ]);
        } else {
            Log::error("FB: Firebase user unsubscription for user {$this->email} from store {$this->code} failed with response " . print_r($response, true));
        }
    }

    private function fb_subscribe_to_group($store, $device_id = null)
    {
        if (!isset($device_id))
            $device_id = $this->fb_device_id;

        $s = $this->stores->firstWhere('id', $store->id);
        // User already registered to provided store
        if (!$s || $s->pivot->fb_registered)
            return;

        $response = fb_curl_post([
            "operation" => "add",
            "notification_key_name" => $store->code,
            "notification_key" => $store->fb_registration_key,
            "registration_ids" => [ $device_id ] 
        ]);

        if($response) {
            Log::info("FB: Firebase user subscription for user {$this->email} to store {$this->code} completed with response " . print_r($response, true));
            $this->stores()->updateExistingPivot($store->id, [ 'fb_registered' => true ]);
        } else {
            Log::error("FB: Firebase user subscription for user {$this->email} to store {$this->code} failed with response " . print_r($response, true));
        }
    }

}
