<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class userAdminController extends Controller
{
    public function index()
    {        
        return view('pages.admin.vendorsdashboard');
    }
}
