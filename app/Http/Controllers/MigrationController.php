<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MigrationController extends Controller
{
    public function runMigrations()
    {
        try {
            Artisan::call('migrate');
            return response()->json(['success' => true, 'message' => 'Migrations completed']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
