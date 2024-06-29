<?php

namespace App\Http\Controllers;
use App\Models\seo_key_words;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class homeController extends Controller
{    
    protected $seo_key_words;

    public function __construct(seo_key_words $seo_key_words)
    {
        $this->seo_key_words = $seo_key_words;
    }

    public function index(){
        $getAllSEOData = $this->seo_key_words->where('pbseo_status', '=', '1')->get();
        $data = [
            'seoWords' => $getAllSEOData,
        ];
        return view('pages.home', $data);
        // return view('pages.home')->with('seoWords', $getAllSEOData);
    }
}
