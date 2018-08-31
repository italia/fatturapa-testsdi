<?php

namespace Fatturapa\Libsdi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InvoicesController extends Controller
{
    
    public function index(Request $request)
    {
        $state = $request->input('state');
        echo "<pre>";
        print_r($state);
        exit;
    }
}
