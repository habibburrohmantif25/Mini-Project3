<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\JsonResponse;

class SchoolDistributionController extends Controller
{
    public function index(School $school): JsonResponse
    {
        $distributions = $school->mealDistributions()
            ->orderBy('distribution_date', 'desc')
            ->get()
            ->map(function ($dist) {
                return [
                    'id' => $dist->id,
                    'school_id' => $dist->school_id,
                    'distribution_date' => $dist->distribution_date->format('Y-m-d'),
                    'formatted_date' => $dist->distribution_date->format('d/m/Y'),
                    'menu_description' => $dist->menu_description,
                    'batch_code' => $dist->batch_code,
                ];
            });

        return response()->json($distributions);
    }
}
