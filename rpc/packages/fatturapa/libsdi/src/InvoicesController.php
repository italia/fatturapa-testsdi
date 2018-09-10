<?php

namespace Fatturapa\Libsdi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Models\Invoice;
use Lib\Exchange;

class InvoicesController extends Controller
{
	
    public function index(Request $request)
    {
    	$status = $request->input('status');	
    	$Invoices = Invoice::all()->where('status', $status);
	
		return response()->json(array(            
            'invoices' => $Invoices->toArray()));			    				    		
    }
	public function checkValidity()
	{		
		Exchange::checkValidity();
		echo "Check Validity";
		exit;
	
	}
	

}


