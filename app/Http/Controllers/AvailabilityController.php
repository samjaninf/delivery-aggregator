<?php

namespace App\Http\Controllers;

use App\Availability;
use Bouncer;
use Illuminate\Http\Request;

class AvailabilitiesController extends Controller
{
    public function index()
    {
        if (Bouncer::cannot('manage own availabilities')) {
            abort(401);
        }

        return auth()->user()->availabilities()->paginate();
    }

    public function show($availability)
    {
        if (Bouncer::cannot('manage own availabilities')) {
            abort(401);
        }

        $avail = Availability::findOrFail($availability);

        if ($avail->user !== auth()->user() && Bouncer::cannot('manage others availabilities')) {
            return abort(403);
        }

        return $avail;
    }

    public function create(Request $request)
    {
        if (Bouncer::cannot('manage own availabilities')) {
            abort(401);
        }

        $params = $request->json()->all();
        $avail = new Availability();
        $avail->fill($params);
        $avail->user()->associate(auth()->user());
    }

    public function update(Request $request)
    {
        if (Bouncer::cannot('manage own availabilities')) {
            abort(401);
        }

        $avail = Availability::findOrFail($params['id']);

        if ($avail->user !== auth()->user() && Bouncer::cannot('manage others availabilities')) {
            return abort(403);
        }

        $params = $request->json()->all();
        $avail->fill($params);
        $avail->save();
    }

    public function destroy($availability)
    {
        if (Bouncer::cannot('manage own availabilities')) {
            abort(401);
        }

        $avail = Availability::findOrFail($availability);

        if ($avail->user !== auth()->user() && Bouncer::cannot('manage others availabilities')) {
            return abort(403);
        }

        $avail->delete();
    }
}
