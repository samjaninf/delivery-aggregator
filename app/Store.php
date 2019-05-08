<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Store extends Model
{
    public static function boot()
    {
        parent::boot();

        // When a new store is created, create the required Firebase group too
        static::created(function ($store) {
            $store->fb_create_group();
        });

        // When a store is deleted, delete the Firebase group too
        // and all users relationships
        static::deleting(function ($store) {
            $store->fb_delete_group();
            $store->users()->detach();
        });
    }

    protected $fillable = [
        'name',
        'code',
        'url',
        'consumer_key',
        'consumer_secret',
    ];

    /*******************
     *  RELATIONSHIPS  *
     *******************/

    public function users()
    {
        return $this->belongsToMany('App\User')->withPivot('fb_registered');
    }

    public function statusChanges()
    {
        return $this->hasMany('App\StatusChange');
    }

    public function superstores()
    {
        return $this->belongsToMany('App\Store', 'store_superstore', 'store_id', 'superstore_id');
    }

    public function stores()
    {
        return $this->belongsToMany('App\Store', 'store_superstore', 'superstore_id', 'store_id');
    }

    /**********************
     *  HELPER FUNCTIONS  *
     **********************/

    /**
     * Finds a store by its code
     *
     * @param string $code The code of the desidered store
     */
    public static function findByCode(string $code)
    {
        return Store::where('code', $code)->first();
    }

    /************************
     *  FIREBASE FUNCTIONS  *
     ************************/

    /**
     * Create a Firebase group for this store
     *
     * @param User $user The first user of the store. If null is specified the current logged user will be used
     */
    public function fb_create_group($user = null)
    {
        $user = $user ?? auth()->user();

        // Using CLI: cannot create group
        if (!$user) {
            return;
        }

        $deviceId = $user->fb_device_id;
        if (!$deviceId) {
            Log::warning("FB: Active user doesn't have a valid fb_device_id, cannot create Firebase group!");
            return;
        }

        // Perform the Firebase request with the required parameters
        $response = fb_curl_post([
            "operation" => "create",
            "notification_key_name" => $this->code,
            "registration_ids" => [$deviceId],
        ]);

        // Log result of the Firebase request
        if ($response && !isset($response->error)) {
            Log::info("FB: Firebase group creation for store {$this->code} completed with response " . print_r($response, true));
            $this->fb_notification_key = $response->notification_key;
            $this->save();
        } else {
            Log::error("FB: Firebase group creation for store {$this->code} failed with response " . print_r($response, true));
        }
    }

    /**
     * Delete the Firebase group for this store
     */
    public function fb_delete_group()
    {
        // Make sure that the store has a registered Firebase group
        if (!$this->fb_notification_key) {
            Log::warning("FB: Store {$this->code} doesn't have a valid fb_notification_key, ignoring request to delete group from Firebase!");
            return;
        }

        // Deleting all the registered devices deletes the group
        $deviceIds = $this->users->pluck('fb_device_id')->filter(function ($id) {return isset($id);});

        // Perform the Firebase request with the required parameters
        $response = fb_curl_post([
            "operation" => "remove",
            "notification_key_name" => $this->code,
            "notification_key" => $this->fb_notification_key,
            "registration_ids" => $deviceIds,
        ]);

        // Log result of the Firebase request
        if ($response && !isset($response->error)) {
            Log::info("FB: Firebase group deletion for store {$this->code} completed with response " . print_r($response, true));
        } else {
            Log::error("FB: Firebase group deletion for store {$this->code} failed with response " . print_r($response, true));
        }
    }
}
