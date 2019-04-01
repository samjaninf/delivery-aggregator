<?php

namespace App\Http\GraphQL\Queries;

use App\Services\WooServiceGQL;
use App\Store;
use GraphQL\Type\Definition\ResolveInfo;

class Products
{
    public function __construct(WooServiceGQL $woo)
    {
        $this->woo = $woo;
    }

    public function resolve($rootValue, array $args, $context, ResolveInfo $resolveInfo)
    {
        $id = $args['id'];
        $count = $args['count'];
        $page = $args['page'] ?? 1;

        $store = Store::findOrFail($id);

        return $this->woo->products($store, $count, $page);
    }
}
