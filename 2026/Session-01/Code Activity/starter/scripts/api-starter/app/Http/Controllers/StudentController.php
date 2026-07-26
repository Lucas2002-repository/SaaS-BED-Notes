<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class StudentController extends Controller
{
    public function index(): JsonResponse
   {
    return response()->json([
        [
            'id' => 1,
            'name' => 'Lucas Bellesini',
            'course' => 'Front-End Web Development',
        ],
        [
            'id' => 2,
            'name' => 'Yukie Luescher',
            'course' => 'Back-End Web Development',
        ],
    ]);
   }
}
