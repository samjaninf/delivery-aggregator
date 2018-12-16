<?php

namespace App\Http\Controllers;

use App\Services\WooService;
use Illuminate\Http\Request;

class ServerStatusController extends Controller
{
    public function __construct(WooService $woo)
    {
        $this->woo = $woo;
    }

    public function check(Request $request, $store)
    {
        try {
            $orders = $this->woo->orders($store);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => 'ok',
        ], 200);
    }
}
