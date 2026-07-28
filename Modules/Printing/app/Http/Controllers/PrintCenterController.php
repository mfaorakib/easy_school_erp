<?php

namespace Modules\Printing\Http\Controllers;

use App\Http\Controllers\Controller;

class PrintCenterController extends Controller
{
    public function index()
    {
        return view('printing::index');
    }
}
