<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use L5Swagger\GeneratorFactory;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SweggerController extends Controller
{
    public function generate()
    {
        try {
            Artisan::call('l5-swagger:generate');

            return response()->json([
                'status' => 'success',
                'message' => 'Swagger docs generated successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
