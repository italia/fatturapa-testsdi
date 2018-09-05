<?php

namespace Fatturapa\Libsdi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Models\Invoice;
use Lib\Exchange;
use Lib\Base;


class BaseController extends Controller
{
	
    public function index(Request $request)
    {
    	
    				    			
    }
	public function clear()
	{
		Base::clear();		
		echo "clear";
		exit;
	}
	public function settimestamp()
	{
		Base::setTimeStamp('2019-01-01 00:00:00');
		echo "timestamp";
		exit;
	}	
	public function speed()
	{
		Base::setSpeed(3600);
		echo "speed";
		exit;
	}
	public function gettimestamp()
	{
		echo Base::getTimeStamp();		
		exit;
	}

}
