<?php

namespace App\Http\GraphQL\Queries;

use App\Services\WooServiceGQL;
use App\Store;
use GraphQL\Type\Definition\ResolveInfo;

class Orders
{
    public function __construct(WooServiceGQL $woo)
    {
        $this->woo = $woo;
    }

    public function resolve($rootValue, array $args, $context, ResolveInfo $resolveInfo)
    {
        $storeId = $args['storeId'];
        $count = $args['count'];
        $page = $args['page'] ?? 1;

        $store = Store::findOrFail($storeId);

        return $this->woo->orders($store, $count, $page);
    }
}
