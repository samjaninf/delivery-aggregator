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

        // If the Firebase device ID changed (user changed device) update the group registration
        static::updating(function ($user) {
            if ($user->getOriginal('fb_device_id') != $user->fb_device_id) {
                // Update the fb registration groups with the new device id
                $user->fb_update_groups();
            }
        });

        // When the user is deleted detach its relationships with the stores
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

    /*******************
     *  RELATIONSHIPS  *
     *******************/

    public function stores()
    {
        return $this->belongsToMany('App\Store')->withPivot('fb_registered');
    }

    public function statusChanges()
    {
        return $this->hasMany('App\StatusChange');
    }

    /********************
     *  AUTH FUNCTIONS  *
     ********************/

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    /************************
     *    ROLES FUNCTIONS   *
     ************************/

    /**
     * Get list of the abilities of the user.
     * Admins just have the "admin" ability
     */
    public function getAbilitiesAttribute()
    {
        if ($this->isAn('admin')) {
            return ['admin'];
        } else {
            // Return list of abilities name
            $this->getAbilities()
                ->map(function ($a) {
                    return $a->name;
                });
        }
    }

    /**
     * Checks if the user has permissions a specific store
     *
     * @param Store $store The store we want to check
     *
     * @return bool Whether the user has the required permissions for the specified store
     */
    public function hasPermissionForStore($store)
    {
        return $this->stores()->where('stores.id', $store->id)->first() !== null;
    }

    /************************
     *  FIREBASE FUNCTIONS  *
     ************************/

    /**
     * Re-subscribe the user to his store groups.
     * Necessary when the user changes his Firebase device ID
     */
    public function fb_update_groups()
    {
        $old_device_id = $this->getOriginal('fb_device_id');

        // If the Firebase device ID changed unsubscribe the user from all of his store groups
        if (isset($old_device_id) && $old_device_id !== $this->fb_device_id) {
            $this->fb_unsubscribe_from_groups($old_device_id);
        }

        // If the new Firebase device ID is valid subscribe the user to store groups he has access to
        if ($this->fb_device_id) {
            $this->fb_subscribe_to_groups();
        }
    }

    /**
     * Unsubscribe the user from all of his store groups
     *
     * @param string $device_id The Firebase device ID to unsubscribe from groups
     *                          If null is specified use the current user one
     */
    public function fb_unsubscribe_from_groups($device_id = null)
    {
        $device_id = $device_id ?? $this->fb_device_id;

        // Unsubscribe the user from each store he has access to
        foreach ($this->stores as $store) {
            $this->fb_unsubscribe_from_group($store, $device_id);
        }
    }

    /**
     * Subscribe the user to all of the store groups he has access to
     *
     * @param string $device_id The Firebase device ID to subscribe to groups
     *                          If null is specified use the current user one
     */
    public function fb_subscribe_to_groups($device_id = null)
    {
        $device_id = $device_id ?? $this->fb_device_id;

        // Subscribe the user to each store he has access to
        foreach ($this->stores as $store) {
            $this->fb_subscribe_to_group($store, $device_id);
        }
    }

    /**
     * Unsubscribe the user from the group of a specific store
     *
     * @param Store  $store     The store we want to unsubscribe the user from
     * @param string $device_id The Firebase device ID to unsubscribe from the group
     *                          If null is specified use the current user one
     */
    public function fb_unsubscribe_from_group($store, $device_id = null)
    {
        if (!isset($device_id)) {
            $device_id = $this->fb_device_id;
        }

        $s = $this->stores->firstWhere('id', $store->id);

        // Make sure that the user was already registered to the store group
        if (!$s || !$s->pivot->fb_registered) {
            return;
        }

        // Both Firebase device ID and the store Firebase notification key are needed
        if (!$device_id || !$store->fb_notification_key) {
            return;
        }

        // Perform the Firebase request with the required parameters
        $response = fb_curl_post([
            "operation" => "remove",
            "notification_key_name" => $store->code,
            "notification_key" => $store->fb_notification_key,
            "registration_ids" => [$device_id],
        ]);

        // Log result of the Firebase request
        if ($response && !isset($response->error)) {
            Log::info("FB: Firebase user unsubscription for user {$this->email} from store {$store->code} completed with response " . print_r($response, true));
            $this->stores()->updateExistingPivot($store->id, ['fb_registered' => false]);
        } else {
            Log::error("FB: Firebase user unsubscription for user {$this->email} from store {$store->code} failed with response " . print_r($response, true));
        }
    }

    /**
     * Subscribe the user to the group of a specific store
     *
     * @param Store  $store     The store we want to subscribe the user to
     * @param string $device_id The Firebase device ID to unsubscribe from the group
     *                          If null is specified use the current user one
     */
    public function fb_subscribe_to_group($store, $device_id = null)
    {
        if (!isset($device_id)) {
            $device_id = $this->fb_device_id;
        }

        $s = $this->stores->firstWhere('id', $store->id);
        // Make sure that the user isn't already registered to the store group
        if ($s && $s->pivot->fb_registered) {
            return;
        }

        // Both Firebase device ID and the store Firebase notification key are needed
        if (!$device_id || !$store->fb_notification_key) {
            return;
        }

        // Perform the Firebase request with the required parameters
        $response = fb_curl_post([
            "operation" => "add",
            "notification_key_name" => $store->code,
            "notification_key" => $store->fb_notification_key,
            "registration_ids" => [$device_id],
        ]);

        // Log result of the Firebase request
        if ($response && !isset($response->error)) {
            Log::info("FB: Firebase user subscription for user {$this->email} to store {$store->code} completed with response " . print_r($response, true));
            $this->stores()->updateExistingPivot($store->id, ['fb_registered' => true]);
        } else {
            Log::error("FB: Firebase user subscription for user {$this->email} to store {$store->code} failed with response " . print_r($response, true));
        }
    }

}
