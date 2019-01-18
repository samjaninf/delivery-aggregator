<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Silber\Bouncer\Database\HasRolesAndAbilities;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use HasRolesAndAbilities;

    public static function boot()
    {
        parent::boot();

        static::updating(function ($user) {
            if ($user->getOriginal('fb_device_id') != $user->fb_device_id) {
                // Update the fb registration groups with the new device id
                $user->fb_update_groups();
            }
        });

        static::deleting(function ($user) {
            $user->stores()->detach();
        });
    }

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password',
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

    public function statusChanges()
    {
        return $this->hasMany('App\StatusChange');
    }

    /************************
     *    ROLES FUNCTIONS   *
     ************************/

    public function getAbilitiesAttribute()
    {
        return $this->isAn('admin') ?
        ['admin'] :
        $this->getAbilities()
            ->map(function ($a) {
                return $a->name;
            });
    }

    public function hasPermissionForStore($store)
    {
        return $this->stores()->where('stores.id', $store->id)->first() !== null;
    }

    /************************
     *  FIREBASE FUNCTIONS  *
     ************************/

    public function fb_update_groups()
    {
        $old_device_id = $this->getOriginal('fb_device_id');
        if (isset($old_device_id) && $old_device_id !== $this->fb_device_id) {
            $this->fb_unsubscribe_from_groups($old_device_id);
        }

        if ($this->fb_device_id) {
            $this->fb_subscribe_to_groups();
        }
    }

    public function fb_unsubscribe_from_groups($device_id = null)
    {
        $device_id = $device_id ?? $this->fb_device_id;

        foreach ($this->stores as $store) {
            $this->fb_unsubscribe_from_group($store, $device_id);
        }
    }

    public function fb_subscribe_to_groups($device_id = null)
    {
        $device_id = $device_id ?? $this->fb_device_id;

        foreach ($this->stores as $store) {
            $this->fb_subscribe_to_group($store, $device_id);
        }
    }

    public function fb_unsubscribe_from_group($store, $device_id = null)
    {
        if (!isset($device_id)) {
            $device_id = $this->fb_device_id;
        }

        $s = $this->stores->firstWhere('id', $store->id);
        // User not registered to provided store
        if (!$s || !$s->pivot->fb_registered) {
            return;
        }

        if (!$device_id || !$store->fb_notification_key)
        // need device id and notification key
        {
            return;
        }

        $response = fb_curl_post([
            "operation" => "remove",
            "notification_key_name" => $store->code,
            "notification_key" => $store->fb_notification_key,
            "registration_ids" => [$device_id],
        ]);

        if ($response && !isset($response->error)) {
            Log::info("FB: Firebase user unsubscription for user {$this->email} from store {$store->code} completed with response " . print_r($response, true));
            $this->stores()->updateExistingPivot($store->id, ['fb_registered' => false]);
        } else {
            Log::error("FB: Firebase user unsubscription for user {$this->email} from store {$store->code} failed with response " . print_r($response, true));
        }
    }

    public function fb_subscribe_to_group($store, $device_id = null)
    {
        if (!isset($device_id)) {
            $device_id = $this->fb_device_id;
        }

        $s = $this->stores->firstWhere('id', $store->id);
        // User already registered to provided store
        if ($s && $s->pivot->fb_registered) {
            return;
        }

        if (!$device_id || !$store->fb_notification_key)
        // need device id and notification key
        {
            return;
        }

        $response = fb_curl_post([
            "operation" => "add",
            "notification_key_name" => $store->code,
            "notification_key" => $store->fb_notification_key,
            "registration_ids" => [$device_id],
        ]);

        if ($response && !isset($response->error)) {
            Log::info("FB: Firebase user subscription for user {$this->email} to store {$store->code} completed with response " . print_r($response, true));
            $this->stores()->updateExistingPivot($store->id, ['fb_registered' => true]);
        } else {
            Log::error("FB: Firebase user subscription for user {$this->email} to store {$store->code} failed with response " . print_r($response, true));
        }
    }

}
