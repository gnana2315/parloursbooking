<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class reportsController extends Controller
{
    public function __construct()
    {
        $user = auth()->user();
    }

    public function index()
    { 
        // user log record
        $log_message = auth()->user()->pbu_name.' redirected to the Vendors List';
        $userlog_data = [
            'pbu_id' => $user->pbu_id,
            'pbul_description' => $log_message,
            'pbul_time' => date('Y-m-d H:i:s'),
            'pbul_status' => '1'
        ];
        $this->userLogs->create($userlog_data);

        return view('pages.admin.reports');
    }
}
