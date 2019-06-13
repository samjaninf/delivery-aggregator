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

    /**
     *  Helper methods
     */
    public static function findOverlaps($user, $start, $end)
    {
        $query = Availability::query();

        // if user is provided filter by it
        if ($user != null) {
            $query->where('user_id', $user->id);
        }

        $query
            ->where('start', '<=', $end)
            ->where('end', '>=', $start);

        return $query->get();
    }
}
