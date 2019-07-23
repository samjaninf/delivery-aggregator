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

        $date = new Carbon(Carbon::parse($params['startDate'])->toDateString()); // going through toDateString to manually
        $endDate = new Carbon(Carbon::parse($params['endDate'])->toDateString()); // force timezone to UTC
        $startH = intdiv($params['startTime'], 100);
        $startM = $params['startTime'] % 100;
        $endH = intdiv($params['endTime'], 100);
        $endM = $params['endTime'] % 100;

        if (Bouncer::cannot('manage others availabilities') && $date->copy()->subHours(48)->isPast()) {
            return abort(422, "Can only create availabilities until 48 hours before their start");
        }

        if ($startH >= 24 || $endH >= 24 || $startM >= 60 || $endM >= 60) {
            abort(422, "Invalid start/end time. Please provice an HHMM number");
        }

        $user = auth()->user();

        $results = [];
        $i = 0; // make sure there aren't too many
        while ($date <= $endDate && $i++ < 100) {

            // calculate availability start/end
            $start = $date->copy()->setTime($startH, $startM);
            $end = $date->copy()->setTime($endH, $endM);

            // find overlaps
            $overlaps = Availability::findOverlaps($user, $start, $end);

            // if there are overlaps merge them with the new availability
            if ($overlaps->isNotEmpty()) {

                // find overlaps min start and max end
                $min = $overlaps->min('start');
                $max = $overlaps->max('end');

                // update start/end
                $start = $start->min($min);
                $end = $end->max($max);

                // delete them
                Availability::whereIn('id', $overlaps->pluck('id'))->delete();
            }

            // create new availability
            $avail = new Availability();
            $avail->start = $start;
            $avail->end = $end;
            $avail->user()->associate($user);
            $avail->save();

            // store a list of results
            $avail->makeHidden(['type', 'user', 'created_at', 'updated_at', 'user_id']);
            $results[] = $avail;

            // process the next day
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

        if (Bouncer::cannot('manage others availabilities') && $avail->end->subHours(48)->isPast()) {
            return abort(422, "Can only delete availabilities until 48 hours before their start");
        }

        $avail->delete();
    }
}
