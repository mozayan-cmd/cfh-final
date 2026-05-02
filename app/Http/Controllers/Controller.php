<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    protected function getRelatedRecords($model): JsonResponse
    {
        if (! method_exists($model, 'getRelatedRecords')) {
            return response()->json([]);
        }

        $related = $model->getRelatedRecords();

        return response()->json($related);
    }
}
