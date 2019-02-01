<?php

namespace App\Http\Controllers;

use App\Services\WooService;
use Illuminate\Http\Request;

class ServerStatusController extends Controller
{
    public function __construct(WooService $woo)
    {
        // Inject a WooService instance to use inside this controller
        $this->woo = $woo;
    }

    /**
     * Check if the store API is online and its orders are parsed correctly
     */
    public function check(Request $request, $store)
    {
        try {
            $orders = $this->woo->orders($store);
        } catch (\Exception $e) {
            // Return some diagnostic data
            return response()->json([
                'status' => 'error',
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
            ], 500);
        }

        // Everything is ok
        return response()->json([
            'status' => 'ok',
        ], 200);
    }
}
