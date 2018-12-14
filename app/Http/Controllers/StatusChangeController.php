<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\StatusChange;

class StatusChangeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        if (!auth()->user()->is_admin)
            abort(401);

        return StatusChange::with([
            'user' => function($q){
                $q->select('id', 'name');
            },
            'store' => function($q){
                $q->select('id', 'name');
            },            
        ])
            ->orderBy('updated_at', 'desc')
            ->paginate(20);
    }
}
