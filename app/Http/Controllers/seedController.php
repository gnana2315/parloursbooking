<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class seedController extends Controller
{
    public function seedFromController()
    {
        // Call the db:seed command
        Artisan::call('db:seed');

        return response()->json([
            'message' => 'Database seeded successfully from controller!'
        ]);
    }
}
