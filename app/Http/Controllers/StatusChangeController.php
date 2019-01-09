<?php

namespace App\Http\Controllers;

use App\StatusChange;
use Bouncer;
use Illuminate\Http\Request;

class StatusChangeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        if (Bouncer::cannot('view statuslog')) {
            abort(401);
        }

        $q = StatusChange::query();

        $filter = $request->filter;
        if ($filter) {
            // Simple filtering

            $tokens = explode(' ', $filter);
            foreach ($tokens as $token) {
                // Each token needs to match

                $q->where(function ($q) use ($token) {
                    $q->orWhere('order', 'like', "%$token%");
                    $q->orWhereRaw("DATE_FORMAT(CONVERT_TZ(updated_at, '+00:00', '+01:00'),'%d/%m/%Y %H:%i') LIKE ?", ["%$token%"]);
                    $q->orWhereHas('user', function ($q) use ($token) {
                        $q->where('name', 'like', "%$token%");
                    });
                    $q->orWhereHas('store', function ($q) use ($token) {
                        $q->where('name', 'like', "%$token%");
                    });
                });
            }
        }

        return $q->with([
            'user' => function ($q) {
                $q->select('id', 'name');
            },
            'store' => function ($q) {
                $q->select('id', 'name');
            },
        ])
            ->orderBy('updated_at', 'desc')
            ->paginate(20);
    }
}
