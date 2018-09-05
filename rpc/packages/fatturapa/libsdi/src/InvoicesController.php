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
    	$state = $request->input('state');	
    	$Invoices = Invoice::all()->where('status', $state);		
		echo "<pre>";
		print_r($Invoices->toArray());
		exit;
    				    			
    }
	public function checkValidity()
	{		
		Exchange::checkValidity();
		echo "Check Validity";
		exit;
	
	}
	

}


