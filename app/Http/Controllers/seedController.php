<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class seedController extends Controller
{
    public function seedFromController()
    {
        try {            
            // Call the db:seed command
            Artisan::call('db:seed');

            return response()->json([
                'message' => 'Database seeded successfully from controller!'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
