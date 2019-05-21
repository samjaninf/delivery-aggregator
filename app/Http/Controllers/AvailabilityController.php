<?php

namespace App\Http\Controllers;

use App\Availability;
use Bouncer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function index(Request $request)
    {
        if (Bouncer::cannot('manage own availabilities')) {
            abort(401);
        }

        $params = $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after:from',
        ]);

        return auth()->user()
            ->availabilities()
            ->where('start', '>=', $params['from'])
            ->where('end', '<=', $params['to'])
            ->get(['id', 'start', 'end']);
    }

    public function show($availability)
    {
        if (Bouncer::cannot('manage own availabilities')) {
            abort(401);
        }

        $avail = Availability::findOrFail($availability);

        if ($avail->user->id !== auth()->user()->id && Bouncer::cannot('manage others availabilities')) {
            return abort(403);
        }

        return $avail;
    }

    public function store(Request $request)
    {
        if (Bouncer::cannot('manage own availabilities')) {
            abort(401);
        }

        $params = $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after:start',
        ]);

        $avail = new Availability();
        $avail->start = Carbon::parse($params['start']);
        $avail->end = Carbon::parse($params['end']);
        $avail->user()->associate(auth()->user());
        $avail->save();

        return $avail->makeHidden(['type', 'user', 'created_at', 'updated_at', 'user_id']);
    }

    public function destroy($availability)
    {
        if (Bouncer::cannot('manage own availabilities')) {
            abort(401);
        }

        $avail = Availability::findOrFail($availability);

        if ($avail->user->id !== auth()->user()->id && Bouncer::cannot('manage others availabilities')) {
            return abort(403);
        }

        if ($avail->end->isPast()) {
            return abort(422, "Can't delete past availability");
        }

        $avail->delete();
    }
}
