<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use L5Swagger\GeneratorFactory;

class SweggerController extends Controller
{
    public function generate()
    {
        try {
            // Run the Swagger generator programmatically
            $generator = app()->make(GeneratorFactory::class);
            $generator->generateDocs();

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
