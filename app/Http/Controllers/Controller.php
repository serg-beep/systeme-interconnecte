<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function listResponse(Builder $query, Request $request, int $defaultPerPage = 30, int $maxPerPage = 100)
    {
        if ($request->boolean('paginate')) {
            $perPage = min(
                max((int) $request->integer('per_page', $defaultPerPage), 1),
                $maxPerPage
            );

            return response()->json($query->paginate($perPage));
        }

        $limit = $request->integer('limit');
        $query->limit($limit > 0 ? min($limit, $maxPerPage) : $defaultPerPage);

        return response()->json($query->get());
    }
}
