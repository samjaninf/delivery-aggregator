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

    /**
     * View the status change log
     *
     * Status changes are grouped by store and order and display some aggregate data
     * Order additional data (client name, etc) is fetched from WooCommerce
     */
    public function index(Request $request, $store)
    {
        if (Bouncer::cannot('view statuslog')) {
            abort(401);
        }

        $s = Store::findByCode($store);
        if (!$s) {
            // Store not found
            abort(404);
        }

        // Begin creating query using QueryBuilder
        $q = $s->statusChanges();

        $filter = $request->filter;
        if ($filter) {
            // If a filter is specified

            // The filter is split in space separated tokens
            $tokens = explode(' ', $filter);

            foreach ($tokens as $token) {
                // Each of the tokens needs to have a match in either
                // order number, date or user name (the courier one)

                $q->where(function ($q) use ($token) {
                    $q->orWhere('order', 'like', "%$token%");
                    $q->orWhereRaw("DATE_FORMAT(CONVERT_TZ(updated_at, '+00:00', '+01:00'),'%d/%m/%Y %H:%i') LIKE ?", ["%$token%"]);
                    $q->orWhereHas('user', function ($q) use ($token) {
                        $q->where('name', 'like', "%$token%");
                    });
                });
            }
        }

        // Might need improvements, using "hacky" group concats for now
        $response = $q
            ->groupBy('order')
            ->orderByRaw('MAX(created_at) desc')
            ->selectRaw('`order`,
                         GROUP_CONCAT(created_at) as created_ats,
                         GROUP_CONCAT(user_id) as user_ids,
                         GROUP_CONCAT(status) as statuses')
            ->getQuery()
            ->paginate(20); // Laravel will take care of pagination

        // Get a list of the involved orders IDs
        $ids = $response->getCollection()->pluck('order');

        // Fetch WooCommerce data for the required orders
        $orders = $this->woo->ordersWithId($store, $ids)->keyBy('number');

        // Transform the query result to a proper format
        $response->getCollection()->transform(function ($row) use ($orders) {

            // Split the group concats result
            $statuses = collect(explode(',', $row->statuses))
                ->zip(
                    explode(',', $row->created_ats),
                    explode(',', $row->user_ids)
                );

            // Place order data in result row
            $res = [
                'number' => $row->order,
                'order' => $orders[$row->order] ?? null,
            ];

            foreach ($statuses as $status) {
                $user = User::find($status[2]); // This might need a refactor: should be fetched with the main query

                // Add status data to the result row
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
