<?php

namespace App\Http\Controllers;

use App\Models\Egg;
use App\Models\Node;
use App\Models\Product;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class ProductController extends Controller
{
    /**
     * @description get product locations based on selected egg
     * @param Request $request
     * @param Egg $egg
     * @return Collection|JsonResponse
     */
    public function getNodesBasedOnEgg(Request $request, Egg $egg)
    {
        if (is_null($egg->id)) return response()->json('egg id is required', '400');

        #get products that include this egg
        $products = Product::query()->with('nodes')->whereHas('eggs', function (Builder $builder) use ($egg) {
            $builder->where('id', '=', $egg->id);
        })->get();

        $nodes = collect();

        #filter unique nodes
        $products->each(function (Product $product) use ($nodes) {
            $product->nodes->each(function (Node $node) use ($nodes) {
                if (!$nodes->contains('id', $node->id) && !$node->disabled) {
                    $nodes->add($node);
                }
            });
        });

        return $nodes;
    }

    /**
     * @param Node $node
     * @return Collection|JsonResponse
     */
    public function getProductsBasedOnNode(Node $node)
    {
        if (is_null($node->id)) return response()->json('node id is required', '400');

        return Product::query()->whereHas('nodes', function (Builder $builder) use ($node) {
            $builder->where('id' , '=' , $node->id);
        })->get();
    }
}