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

        if (Bouncer::can('manage others availabilities')) {
            // admins
            return Availability::query()
                ->where('start', '>=', $params['from'])
                ->where('end', '<=', $params['to'])
                ->get(['id', 'start', 'end', 'user_id'])
                ->map(function ($a) {
                    $a->user_name = $a->user->name;
                    $a->makeHidden(['user', 'user_id']);
                    return $a;
                });
        } else {
            // couriers
            return auth()->user()
                ->availabilities()
                ->where('start', '>=', $params['from'])
                ->where('end', '<=', $params['to'])
                ->get(['id', 'start', 'end']);
        }

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
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:start',
            'startTime' => 'required|numeric|min:0|max:2359',
            'endTime' => 'required|numeric|min:0|max:2359|gt:startTime',
        ]);

        $date = Carbon::parse($params['startDate']);
        $endDate = Carbon::parse($params['endDate']);
        $startH = intdiv($params['startTime'], 100);
        $startM = $params['startTime'] % 100;
        $endH = intdiv($params['endTime'], 100);
        $endM = $params['endTime'] % 100;

        if ($startH >= 24 || $endH >= 24 || $startM >= 60 || $endM >= 60) {
            abort(422, "Invalid start/end time. Please provice an HHMM number");
        }

        $results = [];
        $i = 0; // make sure there aren't too many
        while ($date <= $endDate && $i++ < 100) {
            $avail = new Availability();

            $avail->start = $date->copy()->setTime($startH, $startM);
            $avail->end = $date->copy()->setTime($endH, $endM);
            $avail->user()->associate(auth()->user());
            $avail->save();

            $avail->makeHidden(['type', 'user', 'created_at', 'updated_at', 'user_id']);
            $results[] = $avail;

            $date->addDay();
        }

        return $results;
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
