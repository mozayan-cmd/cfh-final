<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

trait DeletesWithWarning
{
    protected function getRelatedRecordsJson($model): JsonResponse
    {
        if (! method_exists($model, 'getRelatedRecords')) {
            return response()->json([]);
        }

        $related = $model->getRelatedRecords();

        return response()->json($related);
    }
}
