<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class CourseController extends Controller
{
   public function index(): JsonResponse
   {
    return response()->json([
        [
            'id' => 1,
            'name' => 'Front-End Web Development',
        ],
        [
            'id' => 2,
            'name' => 'Back-End Web Development'
        ],
    ]);
   }
};
