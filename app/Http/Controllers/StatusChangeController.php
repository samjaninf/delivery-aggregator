<?php

namespace App\Http\Controllers;

use App\Services\WooService;
use App\Store;
use App\User;
use Bouncer;
use Illuminate\Http\Request;

class StatusChangeController extends Controller
{
    public function __construct(WooService $woo)
    {
        $this->woo = $woo;
        $this->middleware('auth:api');
    }

    public function index(Request $request, $store)
    {
        if (Bouncer::cannot('view statuslog')) {
            abort(401);
        }

        $s = Store::findByCode($store);
        if (!$s) {
            abort(404);
        }

        $q = $s->statusChanges();

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
                });
            }
        }

        $response = $q
            ->orderBy('updated_at', 'desc')
            ->groupBy('order')
            ->orderByRaw('MAX(created_at) desc')
            ->selectRaw('`order`,
                         GROUP_CONCAT(created_at) as created_ats,
                         GROUP_CONCAT(user_id) as user_ids,
                         GROUP_CONCAT(status) as statuses')
            ->getQuery()
            ->paginate(20);

        $ids = $response->getCollection()->pluck('order');
        $orders = $this->woo->ordersWithId($store, $ids)->keyBy('number');

        // Transform the query result to a proper format
        $response->getCollection()->transform(function ($row) use ($orders) {
            $statuses = collect(explode(',', $row->statuses))
                ->zip(
                    explode(',', $row->created_ats),
                    explode(',', $row->user_ids)
                );

            $res = [
                'number' => $row->order,
                'order' => $orders[$row->order] ?? null,
            ];

            foreach ($statuses as $status) {
                $user = User::find($status[2]); // This might need a refactor
                $res[$status[0]] = [
                    'date' => $status[1],
                    'user' => $user ? $user->name : null,
                ];
            }
            return $res;
        });

        return $response;
    }
}
