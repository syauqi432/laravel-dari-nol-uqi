<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Saham;

class SahamController extends Controller
{
    public function index()
    {
        $sahams = Saham::all();
        return $sahams;
    }
}
